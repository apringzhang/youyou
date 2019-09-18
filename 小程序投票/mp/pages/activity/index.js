// pages/activity/index.js
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
  onLoad: function (param) {
    this.loadReady();
  },

  /**
   * 获取数据详情
   */
  loadReady: function (pullDown = false) {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'activity/get_activity_list.php',
      method: 'POST',
      success: successDodetail,
      fail: showNetworkError
    });
    function successDodetail(res) {
      if (res.data.result != 0) {
        wx.showToast({
          title: res.data.message,
          icon: 'none'
        })
      } else {
        pages.setData({
          activity_list: res.data.data,
        })
        if (pullDown) {
          wx.stopPullDownRefresh();
        }
      }
    }
  },

  /**
   * 跳转链接
   */
  submitActivity: function (e) {
    wx.setStorageSync("activityId", e.currentTarget.dataset.activityid);
    app.activityId = e.currentTarget.dataset.activityid;
    wx.switchTab({
      url: '/pages/index/index'
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
    this.loadReady(true);
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