//index.js
const common = require('../../js/common.js');
//获取应用实例
const app = getApp()

// pages/rule/index.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
    adminImageUrl: app.config.adminImageUrl,
    appid: app.appid
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var page = this;
    common.share(options, function () {
      common.init(page, function (info) {

      });
    });
    wx.request({
      url: app.config.apiUrl + 'activity/get_activity_desc.php',
      data: {
        appid: app.appid,
        activity_id: app.activityId,
      },
      method: 'GET',
      success: getDescSuccess
    });
    function getDescSuccess(res) {
      if (res.data.result != 0) {
        wx.showToast({
          title: res.data.message
        })
      } else {
        page.setData({
          desc: res.data.message.replace(/\\/, '/')
        });
        wx.stopPullDownRefresh();
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
  onShareAppMessage: function (res) {
    return {
      path: '/pages/rule/index?activity_id=' + app.activityId
    }
  }
})