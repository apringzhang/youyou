// pages/message/message.js
const app = getApp();
var common = require('../../js/common.js');
var showNetworkError = common.showNetworkError;
Page({

  /**
   * 页面的初始数据
   */
  data: {
    page: 1,
    imageUrl: app.config.imageUrl,
    detail: '',
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (param) {
    var id = "";
    if (param.id != "") {
      id = param.id;
      this.setData({
        id: id
      });
    } else {
      id = this.data.id;
    }
    this.getlist(param.id);
  },

  /**
   * 获取数据
   */
  getlist: function (id , pullDown = false) {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'guestbook/get_guestbook_list.php',
      method: 'POST',
      data: {
        'sign_id': id,
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

  //执行提交
  doSubmit: function (event) {
    var pages = this;
    var datas = {};
    datas.content = event.detail.value.content;
    datas.session_id = app.sessionId;
    datas.activity_id = app.activityId;
    datas.sign_id = pages.data.id;
 
    wx.request({
      url: getApp().config.apiUrl + 'guestbook/do_guestbook.php',
      method: 'POST',
      data: datas,
      success: successDosub,
      fail: showNetworkError
    });

    function successDosub(res) {
      if (res.data.result != 0) {
        wx.showToast({
          title: res.data.message,
          icon: 'none'
        })
      } else {
        wx.showToast({
          title: '发表成功',
          icon: 'none',
          success: function () {
            pages.setData({
              detail: ''
            })
            pages.getlist(pages.data.id);
          }
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
    var pages = this;
    pages.setData({
      page: this.data.page + 1,
    })
    wx.showLoading({
      title: '加载中'
    })
    wx.request({
      url: getApp().config.apiUrl + 'guestbook/get_guestbook_list.php',
      method: 'POST',
      data: {
        'sign_id': pages.data.id,
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