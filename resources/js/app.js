/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap')

window.Vue = require('vue')
import App from './App.vue'
import VueRouter from 'vue-router'
import VueAxios from 'vue-axios'
import axios from 'axios'
import {routes} from './routes'
import NProgress from 'nprogress'

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

import Navbar from './components/Navbar'

Vue.component('navbar',Navbar)

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

NProgress.configure({easing: 'ease',speed: 500,showSpinner: true})

Vue.use(VueRouter)
Vue.use(NProgress)
Vue.use(VueAxios,axios)

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const router = new VueRouter({
    mode: 'history',
    routes: routes,
    meta: {
        showProgressBar: true
    }
})

function isLoggedIn ()
{
    return localStorage.getItem("auth");
}

router.beforeResolve((to,from,next) =>
{
    if (to.name) {
        NProgress.start()

        if (to.matched.some(record => record.meta.authOnly)) {

            if (!isLoggedIn()) {
                next({
                    path: "/login",
                });
            } else {
                next();

            }

        } else if (to.matched.some(record => record.meta.guestOnly)) {
            if (isLoggedIn()) {
                next({
                    path: "/dashboard",
                });
            } else {
                next();

            }
        } else {
            next();
        }

    }
    next()
})

router.afterEach((to,from) =>
{
    if (to.name) {

        // Complete the animation of the route progress bar.
        NProgress.done();
    }
})

const app = new Vue({
    el: '#app',
    router: router,
    render: (h) => h(App),
})
