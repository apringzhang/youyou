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
      url: app.config.apiUrl + 'login/user_info',
      data: {
        session_id: app.sessionId,
        info: res.detail
      },
      method: 'POST',
      success: successSendUserInfo,
      fail: showNetworkError
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
        wx.switchTab({
          url: '../index/index'
        })
        getusersinfo();
      }
    }
    function getusersinfo() {
      wx.request({
        url: app.config.apiUrl + 'user/get_user_info',
        data: {
          session_id: app.sessionId,
        },
        method: 'POST',
        success: successUserinfoRequest
      })
    }
    function successUserinfoRequest(res) {
      app.userInfo = res.data.message;
    }
  },
})