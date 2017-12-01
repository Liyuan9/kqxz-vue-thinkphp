<template>
  <div class="main set-user">
    <div class="personal-info">
      <h5>个人资料完善&nbsp;&nbsp;<i class="fa fa-edit"></i></h5>
      <hr color="#67626c" size="1">
      <form class="i-form" >
        <div class="form-group">
          <label class="form-label">新密码：</label>
          <div class="form-control">
            <input type="password" @focus="tip = true" @blur="tip = false" name="password" v-model="info.password" @change="pass"/>
          </div>
          <p v-if="tip" class="tip">密码需包含大小字母与数字，且不能少于8位，不能与原始密码‘Aa123456’一致</p>
        </div>
        <div class="form-group">
          <label class="form-label">电话：</label>
          <div class="form-control">
            <input type="text" name="telphone" v-model="info.telphone" @change="telephone" />
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">密保问题：</label>
          <div class="form-control">
            <select name="question" v-model="info.question">
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
          <div class="form-control">
            <input type="text" name="answer" v-model="info.answer">
          </div>
        </div>
        <div class="form-group">
        <button type="button" @click="handel">提交</button>
        </div>
      </form>
    </div>
  </div>
</template>
<script>
import md5 from 'js-md5'
  export default {
    name: 'UpdateInfo',
    data () {
      return {
        info: {
          username: Cookies.get('username'),
          userID: Cookies.get('userID'),
          password: '',
          telphone: '',
          question: '',
          answer: ''
        },
        tip: false
      }
    },
    methods: {
      pass () {
        if (this.info.password.length < 8) {
          this.$toast('密码不能少于8个字符', 'center');
          return;
        }
        if (this.info.password === 'Aa123456') {
          this.$toast.center('密码不能与原始密码一致')
          return;
        }
        let regex = /^(?:(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])).{8,12}$/;
        if (!regex.test(this.info.password)) {
          this.$toast('密码需包含大小写字母与数字', 'center');
          return;
        }
      },
      telephone () {
        if (this.info.telphone.length == 11) {
          var regx = /1[34578]\d{9}$/;
          if (!regx.test(this.info.telphone)) {
            this.$toast.center('手机格式非法');
            return;
          }
        } else {
          this.$toast.center('请正确输入11位手机号');
          return;
        }
      },
      handel () {
        let role = Cookies.get('role');
        this.info.password = md5(this.info.password);
        this.axios.post('base/userSet', this.info).then((data) => {
          if (data.data.code === '200') {
            this.$toast.center(data.data.message);
            setTimeout(() => {
              if (role != 1) {
                this.$router.push('/attence/person');
              } else {
                this.$router.push('/attence');
              }
            }, 2500)
          } else {
            this.$toast.center(data.data.message)
          }
        })
      }
    }
  }
</script>