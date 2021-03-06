<template>
    <div>
        <auth-admin>
            <template v-slot:content>
                <div class="content">
                    <aura-loader v-if="isLoading"></aura-loader>
                    <div class="container">
                        <div class="row justify-content-between">
                            <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8">
                                <h1>Users</h1>
                            </div>

                            <div
                                class="col-xl-4 col-lg-4 col-md-4 col-sm-4 text-right"
                            >
                                <router-link
                                    to="/user/create"
                                    class="btn btn-outline-secondary"
                                    >Create New <i class="fa fa-plus"></i
                                ></router-link>
                            </div>
                        </div>
                    </div>

                    <!--============== Roles Table ================-->
                    <empty-resource v-if="users.length === 0"></empty-resource>

                    <div class="table-responsive mt-5" v-else>
                        <table class="table shadow-sm">
                            <thead class="table-aura text-center">
                                <tr>
                                    <th scope="col">Thumbnail</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Role</th>
                                    <th scope="col">Created at</th>
                                    <th scope="col">Action</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                <tr v-for="user in users.data" :key="user.id">
                                    <td>
                                        <img
                                            :src="
                                                !user.photo
                                                    ? misc.thumbnail
                                                    : user.photo
                                            "
                                            class="rounded article-img"
                                            width="50px"
                                            height="50px"
                                            alt="thumbnail"
                                        />
                                    </td>
                                    <td>
                                        {{ user.name }}
                                    </td>
                                    <td>{{ user.email }}</td>
                                    <td>{{ user.role.name }}</td>
                                    <td>
                                        {{ user.created_at | formatDate }}
                                    </td>
                                    <td>
                                        <!-- =====Remove user ======= -->
                                        <button
                                            class="btn btn-sm btn-aura"
                                            v-if="user.deleted_at === null"
                                            @click.prevent="removeUser(user.id)"
                                        >
                                            <i class="fa fa-trash"></i>
                                        </button>

                                        <!-- =====Restore Role ======= -->
                                        <button
                                            class="btn btn-sm btn-aura"
                                            v-else
                                            @click.prevent="
                                                restoreUser(user.id)
                                            "
                                        >
                                            <i class="fa fa-undo"></i>
                                        </button>
                                        <!-- =====Edit Article ======= -->
                                        <button
                                            class="btn btn-sm btn-aura"
                                            id="show-modal"
                                            @click="showEditModal(user)"
                                        >
                                            <i class="fa fa-edit"></i>
                                        </button>
                                    </td>

                                    <td>
                                        <span
                                            class="badge badge-pill badge-success"
                                            v-if="user.deleted_at === null"
                                            >Active</span
                                        >
                                        <span
                                            class="badge badge-pill badge-danger"
                                            v-else
                                            >Deleted</span
                                        >
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- ===== Pagination ====== -->
                        <div class="d-flex container mt-5">
                            <pagination
                                class="btn-aura"
                                :data="users"
                                @pagination-change-page="getUsers"
                            >
                                <span slot="prev-nav">Previous </span>
                                <span slot="next-nav">Next</span>
                            </pagination>
                        </div>
                    </div>

                    <!-- =========Edit Modal Component========= -->
                    <modal v-if="showModal" @close="showModal = false">
                        <h5 slot="header" class="text-dark text-center">
                            Edit User
                        </h5>
                        <div slot="body">
                            <form class="fade-in">
                                <validation-error
                                    :errors="validationErrors"
                                    v-if="validationErrors"
                                ></validation-error>

                                <!-- Name Field -->
                                <div class="mt-5">
                                    <h5 class="input-label">Name</h5>
                                    <div class="form-group">
                                        <input
                                            type="text"
                                            class="input-control form-control"
                                            name="name"
                                            v-model="formFields.name"
                                            id="name"
                                            autocomplete="off"
                                            placeholder="Please Provide User's Name"
                                        />
                                    </div>
                                </div>

                                <!-- Description Field -->
                                <div class="mt-4">
                                    <h5 class="input-label">Email</h5>
                                    <div class="form-group">
                                        <input
                                            type="text"
                                            class="input-control form-control"
                                            name="name"
                                            v-model="formFields.email"
                                            id="name"
                                            autocomplete="off"
                                            placeholder="Please Provide a Valid Email Address"
                                        />
                                    </div>
                                </div>

                                <!-- Password Field -->
                                <div class="mt-4">
                                    <h5 class="input-label">Password</h5>
                                    <div class="form-group">
                                        <input
                                            type="text"
                                            class="input-control form-control"
                                            name="name"
                                            v-model="formFields.password"
                                            id="name"
                                            autocomplete="off"
                                            placeholder="Please Provide a Secured Password"
                                        />
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <h5 class="input-label">
                                        Select User Role
                                    </h5>
                                    <v-select
                                        label="name"
                                        :options="roles"
                                        :reduce="role => role.id"
                                        v-model="formFields.role_id"
                                        aria-placeholder="Please select user role"
                                    >
                                    </v-select>
                                </div>

                                <!-- Image Fields -->
                                <div class="container-fluid p-0 m-0">
                                    <div class="row">
                                        <div class="col-md-4 mt-5">
                                            <h5 class="input-label">
                                                Choose a Cover photo
                                            </h5>
                                            <input
                                                type="file"
                                                @change="selectFile"
                                                id="photo"
                                                name="photo"
                                                class="form-control input-control py-1"
                                            />
                                        </div>

                                        <div class="col-md-4 text-center mt-5">
                                            <img
                                                :src="
                                                    !formFields.photo
                                                        ? misc.thumbnail
                                                        : formFields.photo
                                                "
                                                class="img-responsive rounded-circle"
                                                height="80"
                                                width="90"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <!-- Control Button -->
                                <div class="container-fluid mt-5 d-flex p-0">
                                    <!-- Create -->
                                    <div class="ml-4">
                                        <button
                                            class="btn btn-md btn-success"
                                            @click.prevent="editUser"
                                        >
                                            SAVE
                                            <i class="fa fa-edit"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </modal>
                </div>
            </template>
        </auth-admin>
    </div>
</template>

<script>
import User from "../../../apis/admin/User";
import Role from "../../../apis/admin/Role";
export default {
    name: "Users",
    data: () => ({
        users: {},
        isLoading: false,
        showModal: false,
        validationErrors: "",
        formFields: {
            name: "",
            email: "",
            role_id: "Please select role",
            password: "",
            photo: "",
            id: ""
        },
        misc: {
            thumbnail:
                "https://res.cloudinary.com/cre8tive-technologies/image/upload/v1604580085/aura/Preview-icon_mzat3j.png"
        },
        roles: [],
        supportedFiles: ["image/jpeg", "image/jpg", "image/png"]
    }),
    mounted() {
        this.getUsers();
        this.getRoles();
    },
    methods: {
        /* -------------------------------------------------------------------------- */
        /*                               Get Users                                */
        /* -------------------------------------------------------------------------- */
        getUsers(page) {
            this.isLoading = true;
            if (typeof page === "undefined") {
                page = 1;
            }
            User.listUsers(page)
                .then(response => {
                    this.isLoading = false;
                    this.users = response.data.users;
                })
                .catch(err => {
                    this.isLoading = false;
                    let message = err.response.data.message;
                    this.alertError(message);
                });
        },

        /* -------------------------------------------------------------------------- */
        /*                               Remove User                               */
        /* -------------------------------------------------------------------------- */

        removeUser(user) {
            User.removeUser(user)
                .then(response => {
                    if (response.status == 200) {
                        this.alertSuccess(response.data);
                    }
                    //Fetch users
                    this.getUsers();
                })
                .catch(error => {
                    if (error.response.status == 404) {
                        this.alertError(
                            "The user you are trying to remove does not exist."
                        );
                    }
                    console.error(error);
                });
        },

        /* -------------------------------------------------------------------------- */
        /*                               Restore User                               */
        /* -------------------------------------------------------------------------- */
        restoreUser(user) {
            User.restoreUser({
                id: user
            })
                .then(response => {
                    if (response.status == 200) {
                        this.alertSuccess(response.data);
                    }
                    //Fetch role
                    this.getUsers();
                })
                .catch(error => {
                    if (error.response.status == 404) {
                        this.alertError(
                            "The user you are trying to restore does not exist."
                        );
                    }
                    console.error(error);
                });
        },

        /* -------------------------------------------------------------------------- */
        /*                               Edit User                               */
        /* -------------------------------------------------------------------------- */
        editUser() {
            console.log(this.formFields);
            User.editUser(this.formFields, this.formFields.id)
                .then(response => {
                    this.showModal = false;
                    if (response.status == 200) {
                        this.alertSuccess(response.data);
                    }
                    //Fetch article
                    this.getRoles();
                })
                .catch(error => {
                    this.showModal = false;
                    if (error.response.status == 404) {
                        this.alertError(
                            "The article you are trying to update does not exist."
                        );
                    } else if (error.response.status == 403) {
                        this.alertError(
                            "You do not have permission to perform this operation"
                        );
                    }
                    console.error(error);
                });
        },

        /* -------------------------------------------------------------------------- */
        /*                               Toast Alerts                               */
        /* -------------------------------------------------------------------------- */
        alertError(message) {
            Vue.$toast.open({
                message: message,
                type: "error",
                position: "top-right"
            });
        },

        alertWarning(message) {
            Vue.$toast.open({
                message: message,
                type: "warning",
                position: "top-right"
            });
        },

        alertSuccess(message) {
            Vue.$toast.open({
                message: message,
                type: "success",
                position: "top-right"
            });
        },

        /* -------------------------------------------------------------------------- */
        /*                                  Get Roles                                 */
        /* -------------------------------------------------------------------------- */

        getRoles() {
            this.isLoading = true;
            Role.listRoles()
                .then(response => {
                    this.isLoading = false;
                    this.roles = response.data.roles;
                    console.log(this.roles);
                })
                .catch(err => {
                    this.isLoading = false;
                    let message = err.response.data.message;
                    this.alertError(message);
                });
        },

        /* -------------------------------------------------------------------------- */
        /*                       SHOW MODAL AND SET FORM FIELDS                       */
        /* -------------------------------------------------------------------------- */
        showEditModal(role) {
            this.showModal = true;
            this.formFields = role;
        },

        /* -------------------------------------------------------------------------- */
        /*                               IMAGE FUNCTIONS                              */
        /* -------------------------------------------------------------------------- */
        selectFile(e) {
            let files = e.target.files || e.dataTransfer.files;
            if (!files.length) return;
            if (
                this.supportedFiles.includes(files[0]["type"]) &&
                files[0]["size"] < 3e6
            ) {
                this.createImage(files[0]);
            } else {
                this.alertError(
                    "Oops! File Type not Supported OR File too Large [3MB]."
                );
            }

            console.log(this.formFields.photo);
        },

        createImage(file) {
            let reader = new FileReader();
            let vm = this;
            reader.onload = e => {
                vm.formFields.photo = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
};
</script>
