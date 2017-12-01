<template>
  <div class="main-content">
    <div class="top">
      <h4><i class="fa fa-paper-plane"></i>您的位置：用户中心</h4>
    </div>
    <div class="content">
      <div class="tool">
        <ul class="search">
          <li>
            <select name="type" v-model="type">
              <option value="">搜索</option>
              <option value="id">工号</option>
              <option value="username">姓名</option>
            </select>
          </li>
          <li>
            <input type="text" name="desc" v-model="desc" placeholder="输入搜索关键字" @keyup.enter="search"/>
          </li>
          <li>
            <button type="button" @click="search">搜索</button>
          </li>
          <li><button type="button" @click="del('all')">一键删除</button></li>
        </ul>
      </div>
      <table class="table table-default">
        <thead>
          <tr>
            <th style="width: 30px;"></th>
            <th>工号</th>
            <th>姓名</th>
            <th>部门</th>
            <th>职位</th>
            <th>电话</th>
            <th>在职情况</th>
            <th colspan="3">操作</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in list">
            <td style="width: 30px;"><input type="checkbox" @click="iCheck($event, item.id)" :value="item.id" v-if="item.id != 1"></td>
            <td>{{item.id}}</td>
            <td><i v-if="item.role != 0" class="fa fa-user" style="color:#FF4F1B"></i>&nbsp;&nbsp;{{item.username}}</td>
            <td>{{item.branch}}/{{item.area}}</td>
            <td>{{item.position}}</td>
            <td>{{item.telphone}}</td>
            <td>{{item.status}}</td>
            <td style="width: 30px;"><a href="javascript:;" @click="reset(item.id)" title="重置密码"><i class="fa fa-lock"></i></a></td>
            <td style="width: 30px;"><router-link :to="{name:'attence',params:{type: 'person', userID: item.id}}" title="查看考勤"><i class="fa fa-clock-o"></i></router-link></td>
            <td style="width: 30px;"><a href="javascript:;" @click="del('', item.id)" title="删除" v-if="item.id != 1"><i class="fa fa-trash"></i></a></td>
          </tr>
        </tbody>
      </table>
      <div class="page" v-if="maxPage >1">
        <a class="prev" v-if="nowPage > 1"  @click="page(nowPage-1)">&lt&lt上一页</a>
        <a v-for="(item, index) in pageList" :class="[nowPage === item ? 'now':'','num']" @click="page(item)" >{{item}}</a>
        <a class="next" v-if="nowPage < maxPage" @click="page(nowPage+1)">下一页&gt&gt</a>
      </div>
    </div>
  </div>
</template>
<script>
  import md5 from 'js-md5'
  export default {
    name: 'userlist',
    data () {
      return {
        list: [],
        originList: [],
        filterList: [],
        nowPage: 1,
        maxPage: '',
        role: Cookies.get('role'),
        pageList: [],
        pageNum: 20,
        type: '',
        desc: '',
        delId: []
      }
    },
    mounted () {
      this.axios.get('base/user/type/all').then((data) => {
        if (data.data.code == '200') {
          this.originList = data.data.info;
          this.filterList = data.data.info;
          this.pageCount(1);
        }
      })
    },
    methods: {
      iCheck (e, id) {
        if (e.target.checked) {
          this.delId.push(id);
        } else {
          this.delId = this.delId.filter(t => t != id)
        }
      },
      search () {
        switch (this.type) {
          case 'id':
            this.filterList = this.originList.filter(t => t.id.indexOf(this.desc) >= 0);
            break;
          case 'username':
            this.filterList = this.originList.filter(t => t.username.indexOf(this.desc) >= 0);
            break;
          default:break;
        }
        this.pageCount (1);
      },
      pageCount (now) {
        this.maxPage = Math.ceil(this.filterList.length / this.pageNum);
        this.pageList = [];
        if (this.maxPage > 1) {
          let i = 1;
          while (i <= this.maxPage) {
            this.pageList.push(i);
            i++;
          }
          this.page(now)
        } else {
          this.list = this.filterList
        }
      },
      page (now) {
        let vm = this;
        this.nowPage = now;
        vm.list = vm.filterList.filter(function (item, index) {
          return (index >= (now-1)*vm.pageNum && index < now*vm.pageNum)
        })
      },
      reset (id) {
        let password = md5('Aa123456');
        this.axios.post('base/edit', {id: id, type: 'user', password: password}).then((data) => {
          this.$toast.center(data.data.message);
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
        postData['type'] = 'user';
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
      }
    }
  }
</script>