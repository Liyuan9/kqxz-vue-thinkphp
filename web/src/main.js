import Vue from 'vue'
import Vuex from 'vuex'
import router from './router/index'
import store from './store/index'
import Cookies from 'js-cookie'
import axios from 'axios'
import App from './App'
import '../static/fonts/css/font-awesome.min.css'
import * as filters from '@/util/filters'
import TopToast from 'top-toast'

Vue.use(Vuex)
Vue.use(TopToast)
Vue.config.productionTip = false

// 过滤器过滤
Object.keys(filters).forEach(key => {
    Vue.filter(key, filters[key])
})

Vue.prototype.axios = axios
axios.defaults.timeout = 5000
axios.defaults.baseURL = HOST
axios.defaults.headers['Content-Type'] = 'application/json'

window.HOST = HOST
window.Cookies = Cookies

// 路由跳转前验证用户是否已登录
router.beforeEach((to, from, next) => {
  if (to.meta.Auth) {
    if (Cookies.get('token')) {
        if (to.meta.Role) {
            if (Cookies.get('role') != 0) {
                next()
            } else {
                this.$toast.center('你没有权限进入此页');
                window.history.go(-1);
            }
        } else {
            next()
        }
    } else {
        router.push({ name: 'login' })
    }
  } else {
      next()
  }
})

new Vue({
  router,
  store,
  render: h => h(App)
}).$mount('#app')
