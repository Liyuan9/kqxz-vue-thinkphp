<template>
  <div class="left-aside">
    <ul class="aside">
      <li><h3>运维考勤系统</h3></li>
      <li v-for="(item,index) in menu" :key="index">
        <router-link v-if="!item.child" :to="item.link" ><i :class="item.icon"></i>{{item.name}}</router-link>
        <a v-else href="javascript:;" class="child-down" @click="down($event)"><i :class="item.icon"></i>{{item.name}}</a>
        <ul v-if="item.child" class="child-menu">
          <li v-for="(child,key) in item.child" ><router-link :to="child.link"><i>&nbsp;&nbsp;&nbsp;</i>{{child.name}}</router-link></li>
        </ul>
      </li>
      <li>
        <a @click="logout"><i class="fa fa-sign-out"></i>退出登录</a>
      </li>
    </ul>
  </div>
</template>
<script>
  import $ from 'jquery'
  export default {
    name: 'LeftAside',
    data () {
      return {
        username: Cookies.get('username'),
        menu: {},
      }
    },
    mounted () {
      let role = Cookies.get('role');
      let menu = {};
      if (role == 0) {
        menu[0] = {name: '个人信息', link: '/personal/person', icon: 'fa fa-user'};
        menu[1] = {name: '考勤中心', link: '', icon: 'fa fa-clock-o', child: [{name: '我的考勤', link: '/attence/person'}, {name: '问题考勤', link: '/attence/question'}]};
        // menu[2] = {name: '薪资中心', link: '', icon: 'fa fa-money', child: [{name: '我的薪资', link: '/salary/person'}, {name: '问题薪资', link: '/salary/question'}]};
      } else if (role == 1) {
        menu[0] = {name: '信息中心', link: '', icon: 'fa fa-user', child: [{name: '账号信息', link: '/personal/person'}, {name: '员工信息', link: '/userlist'}, {name: '角色设置', link: '/role'}]};
        menu[1] = {name: '考勤中心', link: '', icon: 'fa fa-clock-o', child:[{name: '员工考勤', link: '/attence'}, {name: '问题考勤', link: '/attence/question'}]};
        // menu[2] = {name: '薪资中心', link: '', icon: 'fa fa-money', child:[{name: '员工薪资', link: '/salary'}, {name: '问题薪资', link: '/salary/question'}]};
        menu[3] = {name: '数据导入', link: '/upload', icon: 'fa fa-upload'};
      } else {
        menu[0] = {name: '信息中心', link: '', icon: 'fa fa-user', child: [{name: '个人信息', link: '/personal/person'}, {name: '员工信息', link: '/userlist'}]};
        menu[1] = {name: '考勤中心', link: '', icon: 'fa fa-clock-o', child:[{name: '我的考勤', link: '/attence/person'}, {name: '员工考勤', link: '/attence'}, {name: '问题考勤', link: '/attence/question'}]};
        // menu[2] = {name: '薪资中心', link: '', icon: 'fa fa-money', child:[{name: '我的薪资', link: '/salary/person'}, {name: '员工薪资', link: '/salary'}, {name: '问题薪资', link: '/salary/question'}]};
        menu[3] = {name: '数据导入', link: '/upload', icon: 'fa fa-upload'};
      }
      this.menu = menu;
    },
    methods: {
      logout () {
        Cookies.remove('username');
        Cookies.remove('token');
        this.$router.push('/login');
      },
      down (e) {
        if (!$(e.target).hasClass('active')) {
          $('.child-menu').slideUp();
          $('.child-down').removeClass('active');
          $(e.target).addClass('active');
          $(e.target).next('.child-menu').slideDown();
        }
      }
    }
  }
</script>
