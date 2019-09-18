App({
  onLaunch: function (options) {
    var app = this;
    //获取activity_id参数
    var activityId = options.query.activity_id;
    if (activityId) {
      wx.setStorageSync("activityId", activityId);
      app.activityId = activityId;
    }
    //初始化
    function init(callback) {
      //检测认证是否过期
      wx.checkSession({
        success: function () {
          app.sessionId = wx.getStorageSync("sessionId");
          if (!app.sessionId) {
            refreshSession();
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
            url: app.config.apiUrl + 'login/code.php',
            data: {
              code: res.code,
              appid: app.appid,
              activity_id: activityId
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
        }
      }
    }
    init(function (error) {
      wx.showToast({
        title: error
      })
    });
  },
  //APPID
  appid: "wxbca9f8bd6e704988",
  //sessionId
  sessionId: null,
  //用户信息
  userInfo: null,
  //活动id
  activityId: null,
  //设置
  config: {
    //接口地址
    "apiUrl": 'https://mp.drinkwall.cn/tongsheng_mp/api/',
    //图片地址
    "imageUrl": 'https://mp.drinkwall.cn/tongsheng_mp/api/upload/image/',
    //视频地址
    "videoUrl": 'https://mp.drinkwall.cn/tongsheng_mp/api/upload/video/',
    //音频地址
    "audioUrl": 'https://mp.drinkwall.cn/tongsheng_mp/api/upload/audio/',
    //后台图片地址
    "adminImageUrl": 'https://mp.drinkwall.cn/tongsheng_mp/web/public/upload/'
  }
})
