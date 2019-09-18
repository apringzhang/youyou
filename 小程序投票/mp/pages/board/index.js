//获取应用实例
const app = getApp();
const common = require('../../js/common.js');

// pages/board/index.js
Page({
  /**
   * 页面的初始数据
   */
  data: {
    //列表数据
    voteList: [],
    //页数
    page: 1,
    //搜索条件
    keyword: '',
    imageUrl: app.config.imageUrl,
    adminImageUrl: app.config.adminImageUrl
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
    this.loadData();
  },

  /**
   * 获取列表
   */
  loadData: function () {
    var page = this;
    wx.request({
      url: app.config.apiUrl + 'apply/get_apply_list.php',
      data: {
        session_id: app.sessionId,
        appid: app.appid,
        activity_id: app.activityId,
        keyword: page.data.keyword,
        page: 1,
        rank: 1
      },
      method: 'POST',
      success: getListSuccess

    });
    function getListSuccess(res) {
      if (res.data.result != 0) {
        wx.showToast({
          title: res.data.message
        })
      } else {
        page.setData({
          voteList: res.data.data
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
    this.setData({
      page: 1
    })
    this.loadData();
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    var page = this;
    //请求列表数据
    this.setData({
      page: this.data.page + 1
    })
    wx.showLoading({
      title: '加载中'
    })
    wx.request({
      url: app.config.apiUrl + 'apply/get_apply_list.php',
      method: 'POST',
      data: {
        page: page.data.page,
        session_id: app.sessionId,
        appid: app.appid,
        activity_id: app.activityId,
        keyword: page.data.keyword,
        rank: 1
      },
      success: successGetList,
    });
    /**
     * 成功请求系列表信息
     */
    function successGetList(res) {
      if (res.data.result != 0) {
        wx.showToast({
          title: res.data.message
        })
      } else {
        page.setData({
          voteList: page.data.voteList.concat(res.data.data)
        });
        wx.hideLoading();
      }
    }
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
    return {
      path: '/pages/board/index?activity_id=' + app.activityId
    }
  }
})