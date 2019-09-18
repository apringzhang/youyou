const app = getApp();
var common = require('../../js/common.js');
var showNetworkError = common.showNetworkError;

Page({

  /**
   * 页面的初始数据
   */
  data: {
    list: [], 
  },
  /**
 * 生命周期函数--监听页面加载
 */
  onLoad: function (options) {
    this.getGouplist();
  },
  checkDel: function (e) {
    var id = e.currentTarget.dataset.id;
    var touid = e.currentTarget.dataset.touid;
    wx.request({
      url: getApp().config.apiUrl + 'message/del_collection',
      method: 'POST',
      data: {
        session_id: app.sessionId,
        touid: touid,
        gid: id,
      },
      success: successGouplist,
      fail: showNetworkError
    });
    this.getGouplist();
    function successGouplist(res) {
      console.log(22);
      if (res.data.result != 0) {
        wx.showToast({
          title: res.data.message,
          icon: 'none'
        })
      } else {
      }
    }
  },
  //获取群成员列表(除管理员)
  getGouplist: function () {
    console.log(11);
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'message/my_collection',
      method: 'POST',
      data: {
        session_id: app.sessionId
      },
      success: successGouplist,
      fail: showNetworkError
    });
    function successGouplist(res) {
      if (res.data.result != 0) {
        wx.showToast({
          title: res.data.message,
          icon: 'none'
        })
      } else {
        pages.setData({
          list: res.data.data,
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