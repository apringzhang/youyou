// pages/list/list.js
const app = getApp();
var common = require('../../js/common.js');
var showNetworkError = common.showNetworkError;
Page({

  /**
   * 页面的初始数据
   */
  data: {
    page: 1,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.getlist();
  },

  /**
   * 获取数据
   */
  getlist: function (pullDown = false) {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'lottery/get_lottery_list.php',
      method: 'POST',
      data: {
        'session_id': app.sessionId,
        'activity_id': app.activityId,
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
    this.getlist(true);
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    var pages = this;
    pages.setData({
      page: this.data.page + 1,
    })
    wx.showLoading({
      title: '加载中'
    })
    wx.request({
      url: getApp().config.apiUrl + 'lottery/get_lottery_list.php',
      method: 'POST',
      data: {
        'session_id': getApp().sessionId,
        'page': pages.data.page,
        'activity_id': app.activityId
      },
      success: successUserlist,
      fail: showNetworkError
    });
    function successUserlist(res) {
      wx.hideLoading();
      //res.data.data.sign_introduce_image = JSON.parse(res.data.data.sign_introduce_image);
      if (res.data.result != 0) {
        wx.showToast({
          title: res.data.message,
          icon: 'none'
        })
      } else {
        var result = pages.data.rel;
        result = result.concat(res.data.data);
        pages.setData({
          rel: result
        })
      }
    }
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
  
  }
})