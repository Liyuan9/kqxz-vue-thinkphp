<template>
  <div class="main-content">
    <div class="top">
      <h4><i class="fa fa-paper-plane"></i>您的位置：个人中心</h4>
    </div>
    <div class="content">
      <div class="panel">
        <div class="panel-heading">
          <h5>个人信息</h5>
        </div>
        <div class="panel-body i-form" style="padding: 20px;" >
          <div class="form-group">
            <label class="form-label">姓名：</label>
            <div class="form-control">
              <input type="text" disabled name="username" :value="info.username">
            </div>
            <div class="set"><a href="javascript:;" @click="change('password')">&nbsp;修改密码？</a></div>
          </div>
          <div class="form-group">
            <label class="form-label">工号：</label>
            <div class="form-control">
              <input type="text" disabled name="id" :value="info.id">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">电话：</label>
            <div class="form-control">
              <input type="text" disabled name="telphone" :value="info.telphone">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">密保问题：</label>
            <div class="form-control">
              <select name="question" disabled  :value="info.question">
                <option value="0">--请选择密保问题--</option>
                <option value="1">1、你的爱好是什么？</option>
                <option value="2">2、你最喜欢的明星是谁？</option>
                <option value="3">3、你的启蒙老师是谁？</option>
                <option value="4">4、你最喜欢吃的水果是什么？</option>
                <option value="5">5、你最喜欢的颜色是什么？</option>
              </select>
            </div>
            <div class="set"><a href="javascript:;" @click="change('question')">修改密保问题？</a></div>
          </div>
          <div class="form-group">
            <label class="form-label">答案：</label>
            <div class="form-control">
              <input type="text" disabled name="answer" :value="info.answer">
            </div>
          </div>
        </div>
      </div>
      <div v-show="show" class="dialog">
        <div class="dialog-content">
          <div class="title">
            <h5>{{title}}</h5>
            <a class="close" @click="show=false" ><i class="fa fa-close"></i></a>
          </div>
          <div class="body i-form forms" style="padding-top:0"></div>
          <div class="footer">
            <button type="button" @click="handel">提交</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
  import $ from 'jquery'
  import md5 from 'js-md5'
  export default {
    name: 'Personal',
    data () {
      return {
        info: [],
        show: false,
        title: '',
        type: ''
      }
    },
    mounted () {
      let userID = Cookies.get('userID');
      this.axios.get('base/user/id/' + userID).then((data) => {
        if (data.data.code == '200') {
          this.info = data.data.info
        } else {
          this.$toast.center(data.data.message)
          setTimeout(() => {
            this.$router.push({name: 'login'})
          }, 2000)
        }
      })
    },
    methods: {
      change (type) {
        let opt = '';
        this.type = type;
        if (type == 'question') {
          this.title = '修改密保问题';
          this.show = true
          opt = `<div class="form-group">
                   <label class="form-label">密保问题：</label>
                   <div class="form-control" style="padding-right:0px">
                     <select name="newQuestion" value="" style="background-color: #fff">
                       <option value="0">--请选择密保问题--</option>
                       <option value="1">1、你的爱好是什么？</option>
                       <option value="2">2、你最喜欢的明星是谁？</option>
                       <option value="3">3、你的启蒙老师是谁？</option>
                       <option value="4">4、你最喜欢吃的水果是什么？</option>
                       <option value="5">5、你最喜欢的颜色是什么？</option>
                     </select>
                   </div>
                 </div>
                 <div class="form-group">
                   <label class="form-label">答案：</label>
                   <div class="form-control" style="padding-right:0px">
                     <input type="text" name="newAnswer" value="" >
                   </div>
                 </div>`;
        } else {
          this.title = '修改密码';
          this.show = true;
          opt = `<div class="form-group">
                    <label class="form-label">新密码:</label>
                    <div class="form-control" style="padding-right:0px">
                      <input type="password" name="newPass" value="">
                    </div>
                 </div>
                 <p class="tip">密码需包含大小写字母与数字，且不能少于8位</p>`
        }
        $('.forms').empty().append(opt)
      },
      handel () {
        let postData = {};
        postData['id'] = this.info.id;
        postData['type'] = 'user'
        if (this.type == 'question') {
          postData['question'] = $('select[name="newQuestion"]').val()
          postData['answer'] = $('input[name="newAnswer"]').val()
        } else {
          let password = $('input[name="newPass"]').val();
          if (password.length < 8) {
            this.$toast('您的密码少于8个字符', 'center');
            return;
          }
          let regex = /^(?:(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])).{8,12}$/;
          if (!regex.test(password)) {
            this.$toast('密码需包含大小写字母与数字', 'center');
            return;
          }
          postData['password'] = md5(password);
        }
        this.axios.post('base/edit', postData).then((data) => {
          if (data.data.code == '200') {
            this.$toast.center(data.data.message)
            setTimeout(() => {
              this.show = false;
              location.reload();
            }, 1000)
          } else {
            this.$toast.center(data.data.message)
          }
        })
      }
    }
  }
</script>
