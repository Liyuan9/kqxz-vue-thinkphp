<template>
  <div class="main-content">
    <div class="top">
      <h4><i class="fa fa-paper-plane"></i>您的位置：考勤中心</h4>
    </div>
    <div class="content">
      <div class="tool">
        <ul class="search">
          <li>
            <select name="type" v-model="type">
              <option value="">搜索</option>
              <option value="userid" v-if="role != 0">工号</option>
              <option value="username" v-if="role != 0">姓名</option>
              <option value="month">时间</option>
            </select>
          </li>
          <li>
            <input type="text" name="desc" v-model="desc" placeholder="输入搜索关键字" @keyup.enter="search"/>
          </li>
          <li>
            <button type="button" @click="search">搜索</button>
          </li>
          <li v-if="role != 0"><button type="button" @click="del('all')">一键删除</button></li>
        </ul>
        <div class="set"><i class="fa fa-cog"></i></div>
      </div>
      <table class="table">
        <thead>
          <tr>
            <th v-if="role != 0" style="width:30px; min-width:30px;" colspan="3">操作</th>
            <th v-else style="width:30px; min-width:30px;" >操作</th>
            <th>确认状态</th>
            <th v-for="(item,index) in tName" :key="index">{{item.column_comment}}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(data,k) in list" :key="k">
            <td v-if="role != 0" style="width:30px; min-width:30px;"><input type="checkbox" @click="iCheck($event, data.id)" :value="data.id"></td>
            <td v-if="role == 1" style="width:30px; min-width:30px;"><a href="javascript:;" @click="del('',data.id)"><i class="fa fa-trash"></i></a></td>
            <td style="width:30px; min-width:30px;"><router-link :to="{name:'detail', params:{id: data.id}}"><i class="fa fa-eye"></i></router-link></td>
            <td v-if="data.status == 2 && data.gstatus != 30" style="color:#ff0000">有问题<a @click="look(data.beizhu)">&nbsp;<i class="fa fa-envelope" style="color:#5373F4"></i></a></td>
            <td v-if="data.status == 2 && data.gstatus == 30" style="color:#ff0000; background-color:#CAE0F5">有问题<a @click="look(data.beizhu)">&nbsp;<i class="fa fa-envelope" style="color:#5373F4"></i></a></td>
            <td v-if="data.status == 0">
              <a href="javascript:;" v-if="data.userid == name" @click="sure(data.id)">确认?</a>
              <p v-else style="color:#BA6F00;">待确认</p>
            </td>
            <td v-if="data.status == 1">没问题</td>
            <td>{{data.username}}</td>
            <td>{{data.userid}}</td>
            <td>{{data.month}}</td>
            <td>{{data.cardday}}</td>
            <td>{{data.restday}}</td>
            <td>{{data.overday}}</td>
            <td>{{data.unusualday}}</td>
            <td>{{data.paidday}}</td>
            <td>{{data.unpaidday}}</td>
            <td>{{data.unpaidhour}}</td>
            <td>{{data.latetime}}</td>
            <td>{{data.leaveearly}}</td>
            <td>{{data.absebteeism}}</td>
            <td>{{data.absentime}}</td>
            <td>{{data.leavetime}}</td>
            <td>{{data.Ctime}}</td>
            <td>{{data.Dtime}}</td>
            <td>{{data.Ntime}}</td>
            <td>{{data.cdnsubsidy}}</td>
            <td>{{data.latededuct}}</td>
            <td>{{data.waterdeduct}}</td>
            <td>{{data.keydeduct}}</td>
            <td>{{data.keyrefund}}</td>
          </tr>
        </tbody>
      </table>
      <div class="page" v-if="maxPage >1">
        <a class="prev" v-if="nowPage > 1"  @click="page(nowPage-1)">&lt&lt上一页</a>
        <a v-for="(item, index) in pages" :class="[nowPage === item ? 'now':'','num']" @click="page(item)" >{{item}}</a>
        <a class="next" v-if="nowPage < maxPage" @click="page(nowPage+1)">下一页&gt&gt</a>
      </div>
    </div>
    <div v-if="dialog.show" v-model="dialog" class="dialog">
      <div class="dialog-content">
        <div class="title">
          <h5>考勤确认</h5>
          <a class="close" @click="dialog.show=false" ><i class="fa fa-close"></i></a>
        </div>
        <div class="body">
          <p>你的意见是？</p>
          <input type="radio" name="status" value="1" v-model="dialog.status"/>&nbsp;没问题
          <input type="radio" name="status" value="2" v-model="dialog.status" style="margin-left:20px;"/>&nbsp;有问题
          <div v-if="dialog.status == 2" class="message">
            <p>请填写问题数据：</p>
            <textarea name="message" v-model="dialog.beizhu" col="3"></textarea>
          </div>
        </div>
        <div v-if="!lookValue" class="footer">
          <button type="button" @click="handelDialog">提交</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'Attence',
    data () {
      return {
        tName: [],
        list: [],   // 当前页数据
        originList: [], // 原始数据
        filterList: [], //过滤数据
        type: '',   // 搜索类型
        desc: '',   // 搜索内容
        month: [],
        pageNum: 20,
        nowPage: 1,
        maxPage: '',
        role: Cookies.get('role'),
        name: Cookies.get('userID'),
        pages: [],
        lookValue: false,
        dialog: {
          show: false,
          id: '',
          status: '',
          beizhu: '',
          type: 'kaoqin'
        },
        delId: []
      }
    },
    mounted () {
      let type = this.$route.params.type
      this.getList(type)
    },
    watch: {
      '$route' (to, from) {
        let type = to.params.type
        this.getList(type)
      }
    },
    methods: {
      iCheck (e, id) {
        if (e.target.checked) {
          this.delId.push(id);
        } else {
          this.delId = this.delId.filter(t => t != id)
        }
      },
      getList (type) {
        let url = '';
        let role = Cookies.get('role');
        let userID = '';
        if (type == 'person') {
          this.$route.params.hasOwnProperty('userID') ? userID = this.$route.params.userID : userID = Cookies.get('userID');
          url = 'base/attence/step/person/user/' + userID;
        } else if (type == 'question') {
          if (role == '0') {
            url = 'base/attence/step/question/user/' + Cookies.get('userID');
          } else {
            url = 'base/attence/step/question'
          }
        } else {
          url = 'base/attence/step/all';
        }
        this.axios.get(url).then((data) => {
          if (data.status === 200) {
            this.tName = data.data.thead;
            this.originList = data.data.list;
            this.filterList = data.data.list;
            this.pageCount(1);
            this.month = data.data.month;
          } else {
            this.$toast.center('网络出错')
          }
        })
      },
      search () {
        let vm = this;
        switch (this.type) {
          case 'userid':
            this.filterList = this.originList.filter(t => t.userid == this.desc)
            break;
          case 'username':
            let vm = this;
            vm.filterList = vm.originList.filter(function(data, index) {
              if (data.username == null) {
                data.username = '';
              }
              return (data.username.indexOf(vm.desc) >= 0)
            })
            break;
          case 'month':
            this.filterList = this.originList.filter(t => t.month.indexOf(this.desc) >= 0)
            break;
          default:
            this.filterList = this.originList
            break;
        }
        if (this.filterList.length < 1) {
          this.$toast.center('没有搜索到匹配数据！');
        }
        this.pageCount(1);
      },
      move (e, elm) {
        let item = e.target.parentNode.firstChild;
        item.style.display = 'block';
        item.style.left = elm + 'px';
      },
      look (elm) {
        this.dialog.show = true;
        this.dialog.status = 2;
        this.dialog.beizhu = elm;
        this.lookValue = true;
      },
      sure (id) {
        this.dialog.show = true;
        this.dialog.id = id;
      },
      handelDialog () {
        if (this.dialog.status == 2 && this.dialog.beizhu == '') {
          this.$toast.center('请填写你的问题数据');
          return;
        }
        if (this.dialog.status == 1) {
          this.dialog.beizhu = '';
        }
        this.axios.post('base/edit', this.dialog).then((data) => {
          if (data.data.code == '200') {
            this.$toast.center(data.data.message)
            setTimeout(() => {
              this.dialog.show = false;
              this.dialog.id = '';
              this.dialog.status = '';
              this.dialog.beizhu = '';
              location.reload();
            }, 1000)
          } else {
            this.$toast.center(data.data.message)
          }
        })
      },
      del (type, id) {
        let postData = {};
        if (type == 'all') {
          if (this.delId.length < 1) {
            this.$toast.center('请勾选要删除的项')
          } else {
            postData['id'] = this.delId;
            postData['step'] = 'all'
          }
        } else {
          postData['id'] = id;
        }
        postData['type'] = 'kaoqin';
        postData['role'] = this.role;
        this.axios.post('base/delete', postData).then((data) => {
          if (data.data.code == '200') {
            this.$toast.center(data.data.message);
            setTimeout(() => {
              location.reload();
            }, 2000)
          } else {
            this.$toast.center(data.data.message);
          }
        })
      },
      pageCount (now) {
        let total = this.filterList.length;
        this.maxPage = Math.ceil(total / this.pageNum);
        this.pages = [];
        if (this.maxPage > 1) {
          let i = 1;
          while (i <= this.maxPage) {
            this.pages.push(i);
            i++;
          }
          this.page(now)
        } else {
          this.list = this.filterList;
        }
      },
      page (now) {
        let data = this.filterList;
        let vm = this;
        this.nowPage = now;
        vm.list = data.filter(function (data, index) {
          return (index < now * vm.pageNum && index >= (now - 1) * vm.pageNum)
        })
      },
    }
  }
</script>