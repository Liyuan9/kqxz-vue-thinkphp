<template>
  <div class="main-content">
    <div class="top">
      <h4><i class="fa fa-paper-plane"></i>您的位置：详情查看</h4>
    </div>
    <div class="content">
      <div class="panel">
        <div class="panel-heading">
          {{title}}考勤详情
        </div>
        <div class="panel-body">
          <table class="detail">
            <tbody>
              <tr v-for="(item, index) in info">
                <td v-if="index == '工号'">姓名：</td>
                <td v-else>{{index}}：</td>
                <td>{{item}}</td>
              </tr>
            </tbody>
          </table>
          <table class="comment">
            <thead>
              <tr>
                <th>考勤意见</th>
                <th>管理员意见</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>
                  <a v-if="comment.status == 0 && name == username" @click="sure(id)">待确认?</a>
                  <p v-if="comment.status == 0 && name != username">待确认</p>
                  <p v-if="comment.status == 1">没问题</p>
                  <p v-if="comment.status == 2" style="color:#ff0000">有问题</p>
                </td>
                <td>
                  <a v-if="comment.gstatus == 30 && role != 0 && comment.status == 2" @click="rep()">待回复?</a>
                  <p v-if="comment.gstatus == 30 && role == 0 && comment.status == 2" >待回复</p>
                  <p v-if="comment.gstatus == 31">同意你反馈的问题</p>
                  <p v-if="comment.gstatus == 32" style="color:#ff0000">你的考勤没有问题</p>
                </td>
              </tr>
              <tr v-if="comment.beizhu != ''">
                <td>原因：{{comment.beizhu}}</td>
                <td><p v-if="comment.gbeizhu">原因：{{comment.gbeizhu}}</p></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div v-if="show" v-model="dialog" class="dialog">
      <div class="dialog-content">
        <div class="title">
          <h5>考勤确认</h5>
          <a class="close" @click="show=false" ><i class="fa fa-close"></i></a>
        </div>
        <div class="body">
          <p>你的意见是？</p>
          <input v-if="replay" type="radio" name="status" value="31" v-model="dialog.status"/>&nbsp;<em v-if="replay">同意你反馈的问题</em>
          <input v-if="replay" type="radio" name="status" value="32" v-model="dialog.status" style="margin-left:20px;"/>&nbsp;<em v-if="replay">你的考勤没有问题</em>
          <input v-if="!replay" type="radio" name="status" value="1" v-model="dialog.status"/>&nbsp;<em v-if="!replay">没问题</em>
          <input v-if="!replay" type="radio" name="status" value="2" v-model="dialog.status" style="margin-left:20px;"/>&nbsp;<em v-if="!replay">有问题</em>
          <div v-if="dialog.status == 2 || dialog.status == 32" class="message">
            <p>理由：</p>
            <textarea name="message" v-model="dialog.beizhu" col="3"></textarea>
          </div>
        </div>
        <div class="footer">
          <button type="button" @click="handelDialog">提交</button>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
  export default {
    name: 'Detail',
    data () {
      return {
        info: [],
        title: '',
        comment: [],
        id: '',
        show: false,
        role: Cookies.get('role'),
        name: '',
        username: Cookies.get('username'),
        dialog: {
          id: '',
          status: '',
          beizhu: '',
          type: 'kaoqin'
        },
        replay: false
      }
    },
    mounted () {
      let id = this.$route.params.id;
      this.id = id;
      this.axios.post('base/detail', {id: id, step: 'attence'}).then((data) => {
        if (data.data.code == '201') {
          this.$toast.center(data.data.message)
        } else {
          this.info = data.data.info
          this.title = data.data.title
          this.comment = data.data.comment
          this.name = data.data.name
        }
      })
    },
    methods: {
      sure (id) {
        this.show = true;
        this.dialog.id = id;
      },
      rep () {
        this.show = true;
        this.replay = true;
      },
      handelDialog () {
        if (this.dialog.status == 2 || this.dialog.status == 32 && this.dialog.beizhu == '') {
          this.$toast.center('请填写你的反馈问题');
          return;
        }
        if (this.dialog.status == 1 || this.dialog.status == 31 ) {
          this.dialog.beizhu = '';
        }
        let data = {};
        if (this.replay) {
          data['id'] = this.id;
          data['gstatus'] = this.dialog.status;
          data['gbeizhu'] = this.dialog.beizhu;
          data['type'] = 'kaoqin'
        } else {
          data = this.dialog;
        }
        this.axios.post('base/edit', data).then((data) => {
          let vm = this;
          if (data.data.code == '200') {
            this.$toast.center(data.data.message)
            if (replay) {
              vm.comment.gbeizhu = vm.dialog.beizhu
              vm.comment.gstatus = vm.dialog.status
            } else {
              vm.comment.beizhu = vm.dialog.beizhu
              vm.comment.status = vm.dialog.status
            }
            setTimeout(() => {
              vm.show = false;
              vm.replay = false
              vm.dialog.id = '';
              vm.dialog.status = '';
              vm.dialog.beizhu = '';
            }, 1000)
          } else {
            this.$toast.center(data.data.message)
          }
        })
      },
    }
  }
</script>
