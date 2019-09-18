// pages/gift/index.js
const app = getApp();
var common = require('../../js/common.js');
var showNetworkError = common.showNetworkError;
Page({

  /**
   * 页面的初始数据
   */
  data: {
    id: 0,
    giftNum: 1,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    common.share(options, function () {
    });
    var id = '';
    if (options.id != '') {
      id = options.id;
      this.setData({
        id : id
      });
    } else {
      id = this.data.id;
    }
    this.userGift(id);
  },

  /**
   * 获取数据
   */
  userGift: function (id, pullDown=false) {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'gift/get_gift_list.php',
      method: 'POST',
      data: {
        'session_id': getApp().sessionId,
        'appid': getApp().appid,
        'activity_id': app.activityId,
        'id': id
      },
      success: successGiftlist,
      fail: showNetworkError
    });
    function successGiftlist(res) {
      if (res.data.result != 0) {
        wx.showToast({
          title: res.data.message,
          icon: 'none'
        })
      } else {
        pages.setData({
          rel: res.data.data,
          imageUrl: app.config.imageUrl,
          adminUrl: app.config.adminImageUrl,
        })
        if (pullDown) {
          wx.stopPullDownRefresh();
        }
      }
    }
  },

  /**
   * 选中礼助力事件
   */
  isClick: function(e) {
    this.setData({
      giftid: e.currentTarget.id,
      price: e.currentTarget.dataset.value,
      num: e.currentTarget.dataset.num,
    })
  },

  /**
   * 滑动选择助力数量
   */
  giftNumChange: function (e) {
    this.setData({
      giftNum: e.detail.value,
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
    this.userGift(this.data.id, true);
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
    return {
      path: '/pages/gift/index?activity_id=' + app.activityId + "&id=" + this.data.id
    }
  },

  /**
   * 提交支付请求
   */
  doSubmit: function () {
    var page = this;
    wx.request({
      url: getApp().config.apiUrl + 'gift/do_order.php',
      method: 'POST',
      data: {
        'session_id': getApp().sessionId,
        'appid': getApp().appid,
        'gift_id': page.data.giftid,
        'gift_num': page.data.giftNum,
        'sign_id': page.data.id,
        'activity_id': app.activityId
      },
      success: successSubmit,
      fail: showNetworkError
    });
    function successSubmit(res) {
      if (res.data.result != 0) {
        wx.showToast({
          title: res.data.message,
          icon: 'none'
        })
      } else {
        wx.requestPayment({
          'timeStamp': res.data.data.timeStamp + "",
          'nonceStr': res.data.data.nonceStr,
          'package': res.data.data.package,
          'signType': res.data.data.signType,
          'paySign': res.data.data.paySign,
          'success': function (res) {
            wx.showToast({
              title: '支付成功'
            })
            page.userGift(page.data.id);
          },
          'fail': function (res) {
            wx.showToast({
              title: '支付失败',
              icon: 'none'
            })
          }
        })
      }
    }
  },
  //跳转到详情页
  toDetail: function toDetail() {
    wx.navigateTo({
      url: '/pages/detail/index?id=' + this.data.id,
    })
  }
})