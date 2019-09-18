const app = getApp();
var Base64 = require('../../js/base64.min.js').Base64;
var common = require('../../js/common.js');
var showNetworkError = common.showNetworkError;
Page({

  /**
   * 页面的初始数据
   */
  data: {
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    if (options.scene) {
      var page = this;
      var scene = Base64.decode(options.scene);
      // var scene = Base64.decode(decodeURIComponent(options.scene));
      // var scene = options.scene;
      console.log(scene);
      //二维码分享入口
      var gid = scene.split('_')[0];
      //app.companyId = sceneCompany.replace('"', "");
      var uid = parseInt(scene.split('_')[1]);
      page.setData({
        gid: gid,
        uid: uid
      })
      if (uid) {
        page.bindinfo()
        // setTimeout(function () {
          
        // }, 1500);
      }


    }
  },

  //获取入群是否审核
  bindinfo: function () {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'group/groups_yn',
      method: 'POST',
      data: {
        g_id: pages.data.gid
      },
      success: successGouplist,
      fail: showNetworkError
    });
    function successGouplist(res) {
      console.log(res);
      if (res.data.result != 0) {
        wx.showToast({
          title: res.data.message,
          icon: 'none'
        })
      } else {
        pages.setData({
          isaudit: res.data.data
        })
      }
    }
  },

  /**
   * 提交理由
   */
  submitMessage:function(event){
    var pages = this;
    console.log(event.detail.value);
    if (pages.data.isaudit == 1) {
      if (!event.detail.value.state) {
        wx.showToast({
          title: '请输入入群理由',
          icon: 'none'
        })
        return;
      }
    }
    
    wx.request({
      url: getApp().config.apiUrl + 'group/create_group',
      method: 'POST',
      data: {
        'g_id': pages.data.gid,
        'session_id': app.sessionId,
        'fromuid': pages.data.uid,
        'reason': event.detail.value.state
      },
      success: successMessageSubmit,
      fail: showNetworkError
    });

    function successMessageSubmit(res) {
      console.log(res);
      if (res.data.result != 0) {
        wx.showToast({
          title: res.data.message,
          icon: 'none'
        })
      } else {
        if (res.data.data == 2) {
          //无需审核入群成功
          wx.showToast({
            title: '您已成功加入该群',
            icon: 'none'
          })
          setTimeout(function () {
            wx.switchTab({
              url: '/pages/surpergroup/surpergroup',
            })
          }, 1500);
        }
        if (res.data.data == 1) {
          //添加邀请记录成功请等待审核
          wx.showToast({
            title: '入群申请已提交,请等待审核',
            icon: 'none'
          })
          setTimeout(function () {
            wx.switchTab({
              url: '/pages/index/index',
            })
          }, 1500);
        }
      }
    }
  },

  bindexc: function () {
    wx.switchTab({
      url: '/pages/index/index',
    })
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})