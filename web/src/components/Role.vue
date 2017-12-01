<template>
  <div class="main-content">
    <div class="top">
      <h4><i class="fa fa-paper-plane"></i>您的位置：角色设置</h4>
    </div>
    <div class="content">
      <table class="table table-default" style="margin-top: 51px;">
        <thead>
          <tr>
            <th>ID</th>
            <th>角色名称</th>
            <th>角色ID</th>
            <th>人员</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in list" v-if="item.role != 0">
            <td>{{item.id}}</td>
            <td>{{item.name}}</td>
            <td>{{item.role}}</td>
            <td style="width: 45%"><span class="user-span" v-for="(user,key) in item.user">{{user.username}}<i class="fa fa-close" @click="remove($event, user.userid)"></i></span></td>
            <td><a>添加角色成员</a></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
<script>
  export default {
    name: 'Role',
    data () {
      return {
        list: [],
      }
    },
    mounted () {
      this.axios.get('base/getrole').then((data) => {
        this.list = data.data;
      });
    },
    methods: {
      remove (e, id) {
        this.axios.post('base/edit', {id: id, type: 'user', role: 0}).then((data) => {
          if(data.data.code == '200') {
            this.$toast.center('已成功移出此人的角色');
            e.target.parentNode.remove();
          }
        })
      }
    }
  }
</script>