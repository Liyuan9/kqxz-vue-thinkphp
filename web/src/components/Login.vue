<template>
  <div class="login">
    <h4 class="title">用户登录</h4>
    <div class="form" >
      <input type="text" placeholder="请输入中文名" name="username" v-model="username"/>
      <input type="password" @focus="setTip" placeholder="请输入密码" name="password" v-model="password" @keyup.enter="login"/>
      <p v-if="tip" class="tip">密码是由大小写字母与数字组成的不少于8位的字符串</p>
      <button type="button" @click="login">登录</button>
    </div>
  </div>
</template>
<script>
import md5 from 'js-md5'
export default {
  name: 'Login',
  data () {
    return {
      username: '',
      password: '',
      tip: false,
    }
  },
  methods: {
    setTip () {
      this.tip = true;
      setTimeout(() =>{
        this.tip = false;
      }, 2000)
    },
    login () {
      if (this.username !== 'admin') {
        let rep = /[\u4e00-\u9fa5]/ ;
        if (!rep.test(this.username)) {
          this.$toast('请输入中文名', 'center');
          return;
        }
      }

      if (this.password.length < 8) {
        this.$toast('您的密码少于8个字符', 'center');
        return;
      }
      let regex = /^(?:(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])).{8,12}$/;
      if (!regex.test(this.password)) {
        this.$toast('密码需包含大小写字母与数字', 'center');
        return;
      }
      this.axios({
        method: 'post',
        url: 'base/login',
        data:{
          username: this.username,
          password: md5(this.password)
        }
      }).then((data) => {
        if (data.data.code == '201') {
          this.$toast.center(data.data.message)
        }else {
          Cookies.set('username', this.username, { expires: 1 })
          Cookies.set('token', data.data.token, { expires: 1 })
          Cookies.set('userID', data.data.userID, { expires: 1 })
          Cookies.set('role', data.data.role, { expires: 1 })
          if(data.data.set){
            this.$router.push({name:'updateInfo'})
          }else{
            this.$router.push({name:'home'})
          }
        }
      })
    }
  }
}
</script>