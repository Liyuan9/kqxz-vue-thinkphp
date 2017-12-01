import Vue from 'vue'
import Router from 'vue-router'
import App from '../App'

const Login = () => import('@/components/Login.vue')
const Upload = () => import('@/components/Upload.vue')
const Attence = () => import('@/components/Attence.vue')
const Personal = () => import('@/components/Personal.vue')
const UpdateInfo = () => import('@/components/UpdateInfo.vue')
const Detail = () => import('@/components/Detail.vue')
const Home = () => import ('@/components/Home.vue')
const UserList = () => import ('@/components/UserList.vue')
const Role = () => import ('@/components/Role.vue')

Vue.use(Router)
const routes = [{
        path: '/',
        redirect: '/login'
    }, {
        path: '/login',
        name: 'login',
        component: Login
    },  {
        path: '/updateInfo',
        name: 'updateInfo',
        meta: {
            Auth: true
        },
        component: UpdateInfo
    }, {
        path: '/home',
        name: 'home',
        meta: {
            Auth: true
        },
        component: Home,
        children: [
            {
                path: '/upload',
                name: 'upload',
                meta: {
                    Auth: true,
                    Role: true
                },
                component: Upload
            }, {
                path: '/attence/:type?',
                name: 'attence',
                meta: {
                    Auth: true
                },
                component: Attence
            }, {
                path: '/personal/:type?',
                name: 'personal',
                meta: {
                    Auth: true
                },
                component: Personal
            }, {
                path: '/userlist',
                name: 'userlist',
                meta: {
                    Auth: true,
                    Role: true
                },
                component: UserList
            }, {
                path: '/detail/:id',
                name: 'detail',
                meta: {
                    Auth: true
                },
                component: Detail
            }, {
                path: '/role',
                name: 'role',
                meta: {
                    Auth: true,
                    Role: true
                },
                component: Role
            }
        ]
    }
]

export default new Router({
  mode: 'history',
  linkActiveClass: 'current',
  scrollBehavior: () => ({ y: 0 }),
  routes: routes
})
