// pages/my/my.js
const app = getApp();
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
    this.getUsers();
  },

  /**
   * 获取数据
   */
  getUsers: function (pullDown = false) {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'users/get_users.php',
      method: 'POST',
      data: {
        'session_id': getApp().sessionId,
        'activity_id': app.activityId
      },
      success: successUserlist,
      fail: showNetworkError
    });
    function successUserlist(res) {
      if (res.data.result != 0) {
        wx.showToast({
          title: res.data.message,
          icon: 'none'
        })
      } else {
        pages.setData({
          rel: res.data.data,
        })
        if (pullDown) {
          wx.stopPullDownRefresh();
        }
      }
    }
  },

  /**
   * 点击删除事件
   */
  isclick: function (e) {
    var pages = this;
    wx.showModal({
      title: '确认提现提示',
      content: '确认提现？',
      success: function (res) {
        if (res.confirm) {
          wx.request({
            url: getApp().config.apiUrl + 'guestbook/del_guestbook.php',
            method: 'POST',
            data: {
              'session_id': app.sessionId,
              'activity_id': app.activityId,
              'id': e.currentTarget.id,
              'sign_id': e.currentTarget.dataset.sign
            },
            success: successUserlist,
            fail: showNetworkError
          });
          function successUserlist(res) {
            if (res.data.result != 0) {
              wx.showToast({
                title: res.data.message,
                icon: 'none'
              })
            } else {
              pages.usersign();
            }
          }
        } else if (res.cancel) {
        }
      }
    })
  },

  /**
   * 收货地址
   */
  myaddress: function () {
    wx.chooseAddress({
      success: function (res) {
        wx.request({
          url: getApp().config.apiUrl + 'address/do_address.php',
          method: 'POST',
          data: {
            'session_id': getApp().sessionId,
            'address': res.userName + ' ' + res.telNumber + ' ' + res.provinceName + res.cityName + res.countyName + res.detailInfo
          },
          success: successUserlist,
          fail: showNetworkError
        });
        function successUserlist(res) {
          if (res.data.result != 0) {
            wx.showToast({
              title: res.data.message,
              icon: 'none'
            })
          } else {
            wx.showToast({
              title: '操作成功',
            })
          }
        }
      }
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
    this.getUsers(true);
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

  },
  /**
   * 获取用户信息按钮
   */
  onGetUserInfo: function (res) {
    var page = this;
    //将用户信息提交至服务器
    var userInfo = res.detail.userInfo;
    wx.request({
      url: app.config.apiUrl + 'login/user_info.php',
      data: {
        session_id: app.sessionId,
        info: res.detail
      },
      method: 'POST',
      success: successSendUserInfo
    });
    //成功将用户信息提交至服务器
    function successSendUserInfo(res) {
      if (res.data.result != 0) {
        wx.showToast({
          title: res.data.message,
          icon: 'none'
        })
      } else {
        app.userInfo = userInfo;
        //avatar_url nick_name
        var currentRel = page.data.rel;
        currentRel.avatar_url = userInfo.avatarUrl;
        currentRel.nick_name = userInfo.nickName;
        page.setData({
          rel: currentRel
        });
      }
    }
  }
})