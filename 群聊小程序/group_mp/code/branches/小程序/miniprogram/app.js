var common = require('/js/common.js');
var socketOpen = false;
var connecting = false;
let SocketTask;
var firstLogin = true;
let grouplist = [];
App({
  globalData: {
    SocketTask: SocketTask
  },
  connectSocket: function () {
    var app = this;
    app.SocketTask = common.webSocket();
    app.SocketTask.onOpen(res => {
      socketOpen = true;
      console.log('监听 WebSocket 连接打开事件。', res);
      connecting = false;
      if (!firstLogin) {
        app.sendmessage();
      }
    })
    app.SocketTask.onClose(onClose => {
      console.log('监听 WebSocket 连接关闭事件。', onClose)
      socketOpen = false;
      wx.showToast({
        title: '请检查网络',
        icon: 'none',
        complete: function () {
          wx.switchTab({
            url: '/pages/index/index',
          });
        }
      });
    })
    app.SocketTask.onError(onError => {
      console.log('监听 WebSocket 错误。错误信息', onError);
      connecting = false;
      socketOpen = false
    })
  },
  checkNetworkInterval: null,
  checkNetwork: function () {
    var app = this;
    if (connecting) {
      return;
    }
    if (!socketOpen) {
      connecting = true;
      app.connectSocket();
      app.onMessagesend();
    }
  },
  onLaunch: function (options) {
    var app = this;
    app.connectSocket();
    if (wx.getStorageSync("grouplist")) {
      grouplist = wx.getStorageSync("grouplist");
    }
    function init(callback) {
      //检测认证是否过期
      wx.checkSession({
        success: function () {
          app.sessionId = wx.getStorageSync("sessionId");
          if (!app.sessionId) {
            refreshSession();
          } else {
            getusersinfo();
          }
        },
        fail: refreshSession
      });
      //刷新session_id
      function refreshSession() {
        wx.login({
          success: successLogin
        });
      }
      //获取code成功
      function successLogin(res) {
        if (res.code) {
          //请求session_id
          wx.request({
            url: app.config.apiUrl + 'login/code',
            data: {
              code: res.code,
              appid: app.appid,
            },
            success: successRequest
          })
        } else {
          callback(res.errMsg);
        }
      }
      //请求session_id成功
      function successRequest(res) {
        if (res.data.result != 0) {
          callback(res.data.message);
        } else {
          wx.setStorageSync("sessionId", res.data.session_id);
          app.sessionId = res.data.session_id;
          app.sendmessage();
          wx.request({
              url: app.config.apiUrl + 'user/get_user_info',
            data: {
              session_id: res.data.session_id,
            },
            method: 'POST',
            success: successUserRequest
          })
        }
      }
      function successUserRequest(res)
      {
        if (res.data.result != 0) {
          wx.showToast({
            title: res.data.message,
            icon: 'none'
          })
        } else {
          app.userInfo = res.data.message;
          app.onMessagesend();
          if (!res.data.message.city)
          {
             wx.redirectTo({
               url: '../getuser/getuser',
             })
          }
        }
      }
      function getusersinfo()
      {
        wx.request({
          url: app.config.apiUrl + 'user/get_user_info',
          data: {
            session_id: app.sessionId,
          },
          method: 'POST',
          success: successUserinfoRequest
        })
      }
    }
    function successUserinfoRequest(res)
    {
        app.userInfo = res.data.message;
        app.sendmessage();
        app.onMessagesend();
    }
    init(function (error) {
      wx.showToast({
        title: error
      })
    });
    
  },

  onMessagesend() {
    var app = this;
    app.SocketTask.onMessage(data => {
      var arr = JSON.parse(data.data);

      if (arr.uid == app.userInfo.id) {
        arr['speaker'] = 'customer';
        if (arr.type == 'audio') {
          arr['vids'] = '../../images/right_voice1.png';
        }
      } else {
        arr['speaker'] = 'server';
        if (arr.type == 'audio') {
          arr['vids'] = '../../images/left_voice1.png';
        }
      }
      if (arr.type == 'text' || arr.type == 'imgs' || arr.type == 'audio') {
        if (!grouplist[arr.group]) {
          grouplist[arr.group] = [];
        }
        if (arr.type == 'audio') {
          var adata = arr.data;
          arr.data = adata.split('_')[0];
          arr['timers'] = Math.ceil(adata.split('_')[1]);
        }
        grouplist[arr.group].push(arr);
        wx.setStorageSync("grouplist", grouplist);
      }
      app.grouplist = grouplist;
      console.log(data);
      console.log(JSON.parse(data.data).data);
      console.log(grouplist);
    })
  },
  
  onShow: function (options) {
    
  },

  sendmessage() {
    var app = this;
    app.SocketTask.send({
      data: JSON.stringify({
        type: 'login',
        session_id: app.sessionId
      }),
      success: function () {
        firstLogin = false;
        clearInterval(app.checkNetworkInterval);
        app.checkNetworkInterval = setInterval(app.checkNetwork, 1000);
      },
      fail: function (res) {
        console.log(res);
      }
    })
    
  },
  SocketTask: null,
  //APPID
  appid: "wx3c829e47aaae3533",
  //sessionId
  sessionId: null,
  //用户信息
  userInfo: null,
  grouplist: [],
  //设置
  config: {
    //接口地址
    // "apiUrl": 'http://192.168.31.102/group_mp/code/branches/admin_program/public/index.php/api/',
    "apiUrl": 'https://groupmp.honorsoftware.cn/index.php/api/',
    //图片地址
    // "imageUrl": 'http://192.168.31.102/group_mp/code/branches/admin_program/public/upload/',
    "imageUrl": 'https://groupmp.honorsoftware.cn/upload/',
    //后台图片地址
    // "adminImageUrl": 'http://192.168.31.102/group_mp/code/branches/admin_program/public/upload/',
    "adminImageUrl": 'https://groupmp.honorsoftware.cn/upload/',
    //语音路径
  //  "audioUrl": 'http://192.168.31.102/group_mp/code/branches/admin_program/public/video/',
    "audioUrl": 'https://groupmp.honorsoftware.cn/video/',
  }
})
