const app = getApp();
var common = require('../../js/common.js');
var showNetworkError = common.showNetworkError;

Page({

  /**
   * 页面的初始数据
   */
  data: {
    info: [],
    imgurl: app.config.imageUrl,
  },
  goBusiness(){
    wx.navigateTo({
      url: '/pages/business/business',
    })
  },
  // goAddress(){
  //   wx.navigateTo({
  //     url: '/pages/address/address',
  //   })
  // },
  goCollection(){
    wx.navigateTo({
      url: '/pages/collection/collection',
    })
  },
  goRecharge(){
    wx.navigateTo({
      url: '/pages/recharge/recharge',
    })
  },
  goRechargevip() {
    wx.navigateTo({
      url: '/pages/viprecharge/viprecharge',
    })
  },
  goTickets(){
    wx.navigateTo({
      url: '/pages/ticketslog/ticketslog',
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.setData({
      info: app.userInfo,
    });
    this.mydian();
  },

  //获取用户信息
  bindinfo: function (pullDown = false) {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'user/get_user_info',
      method: 'POST',
      data: {
        session_id: app.sessionId
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
          info: res.data.message
        })
        if (pullDown) {
          wx.stopPullDownRefresh();
        }
      }
    }
  },

  mydian: function () {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'group/mydian',
      method: 'POST',
      data: {
        session_id: app.sessionId
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
          stamps: res.data.data,
        })
      }
    }
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