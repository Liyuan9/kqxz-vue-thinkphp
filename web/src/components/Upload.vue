<template>
    <div class="main-content">
        <div class="top">
           <h4><i class="fa fa-paper-plane"></i>您的位置：数据导入</h4>
        </div>
        <div class="upload">
            <div class="upload-panel">
                 <div class="panel-heading">
                     考勤导入
                 </div>
                 <div class="panel-body">
                     <p><strong>注意事项：</strong><br>1、导入文件格式：.xls，.xlsx<br>2、文件命名规则“年月名”，如：“201705运维部考勤”<br>3、查看文件导入格式&nbsp;&nbsp;<a href=“”>201705考勤>></a></p>
                     <p style="margin-top:10px;"><strong>考勤导入：</strong><a class="btn btn-primary btn-xs " @click="chooseFile('attence')">选择文件</a></p>
                     <p>已选择文件：<em style="color:red; font-style:normal;">{{attence}}</em></p><p>{{info}}</p>
                     <input type="file" style="display:none" name="attence"  @change="changeFile($event)" ref="attenceInput" />
                 </div>
                 <div class="panel-footer">
                     <a class="btn btn-primary btn-md" @click="upFile('attence')">确认导入</a>
                 </div>
            </div>
            <!--<div class="upload-panel">
                <div class="panel-heading">
                   薪资导入
                </div>
                <div class="panel-body">
                    <p><strong>注意事项：</strong><br>1、导入文件格式：.xls，.xlsx<br>2、文件命名规则“年月名”，如：“201705运维部薪资”<br>3、查看文件导入格式&nbsp;&nbsp;<a href=“”>201705薪资>></a></p>
                    <p style="margin-top:10px;"><strong>薪资导入：</strong><a class="btn btn-primary btn-xs " @click="chooseFile('money')">选择文件</a></p>
                    <p>已选择文件：{{money}}</p>
                    <input type="file" style="display:none" name="money"  @change="changeFile($event)" ref="moneyInput" />
                </div>
                <div class="panel-footer">
                    <a class="btn btn-primary btn-md" @click="upFile('money')">确认导入</a>
                </div>
            </div>-->
        </div>
    </div>
</template>
<script>
  export default {
    name: 'Upload',
    data () {
      return {
        attence: '',
        money: '',
        attenceFile: {},
        moneyFile: {},
        info: ''
      }
    },
    methods: {
      chooseFile (opt) {
        if (opt === 'attence') {
          this.$refs.attenceInput.click();
        } else {
          this.$refs.moneyInput.click();
        }
      },
      changeFile (e) {
        let file = e.target.files[0]
        if (e.target.name === 'attence') {
          this.attence = file.name;
          this.attenceFile = file;
        } else {
          this.money = file.name;
          this.moneyFile = file;
        }
      },
      upFile (opt) {
        let file;
        opt === 'attence' ? file = this.attenceFile : file = this.moneyFile;
        let filename = file.name;
        let arr = filename.split('.');
        if (arr[1] !== 'xls' && arr[1] !== 'xlsx' ) {
          this.$toast.center('文件格式错误！');
          return;
        }
        let fileData = new window.FormData();
        fileData.append('file', file);
        fileData.append('step', opt);
        let xhr = new window.XMLHttpRequest();
        xhr.open('POST', 'http://localhost:999/base/upload', true);
        xhr.send(fileData);
        xhr.onreadystatechange = () => {
          if (xhr.readyState === 4) {
            if (xhr.status === 200) {
              let response = JSON.parse(xhr.response)
              this.$toast.center(response.message)
            } else {
              let error = this.$emit('upload-error', xhr)
              if (error !== false) {
                this.$toast.center(xhr.statusText)
              }
            }
          }
        }
      }
    }
  }
 </script>
