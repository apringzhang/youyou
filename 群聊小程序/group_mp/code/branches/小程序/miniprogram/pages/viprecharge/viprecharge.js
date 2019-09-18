const app = getApp();
var common = require('../../js/common.js');
var showNetworkError = common.showNetworkError;
// pages/recharge/recharge.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
    ordersta: true,
    activeIndex:0,//默认选中第一个
    numArray:[10, 20, 30, 40, 50, 60, 70, 80, 90]
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.getlist()
  },

  //获取列表
  getlist: function (pullDown = false) {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'message/vip_list',
      method: 'POST',
      data: {
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
          items: res.data.message,
        })
        if (pullDown) {
          wx.stopPullDownRefresh();
        }
      }
    }
  },

  //充值
  binsubmit: function (e) {
    var page = this;
    wx.createSelectorQuery().select('.active').boundingClientRect(function (rect) {
      console.log(rect.dataset.price);
      if (page.data.ordersta) {
        page.setData({
          ordersta: false
        })
        page.getdoorder(rect.dataset.price, rect.dataset.tid);
      }
    }).exec()
  },

  //获取订单id
  getdoorder: function (price, torage_id) {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'order/cz_doorder',
      method: 'POST',
      data: {
        session_id: app.sessionId,
        total_amount: price,
        torage_id: torage_id,
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
        pages.setData({
          ordersta: true
        })
      } else {
        pages.setData({
          orderid: res.data.order_id,
        })
        pages.getdopay();
      }
    }
  },

  //唤起支付
  getdopay: function () {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'order_list/cz_pay',
      method: 'POST',
      data: {
        session_id: app.sessionId,
        order_id: pages.data.orderid,
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
        pages.setData({
          ordersta: true
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
              title: '支付成功',
              success: setTimeout(function () {
                wx.switchTab({
                  url: '../personal/personal'
                })
              }, 1500)
            })
          },
          'fail': function (res) {
            wx.showToast({
              title: '支付取消',
              icon: 'none',
              success: setTimeout(function () {
                wx.switchTab({
                  url: '../personal/personal'
                })
              }, 1500)
            })
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
  
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
  
  },
  activethis:function(event){//点击选中事件
      var thisindex = event.currentTarget.dataset.thisindex;//当前index
      this.setData({
        activeIndex:thisindex
      })
  }

})