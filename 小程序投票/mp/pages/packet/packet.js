// pages/packet/packet.js
const app = getApp();
var common = require('../../js/common.js');
var showNetworkError = common.showNetworkError;
Page({

  /**
   * 页面的初始数据
   */
  data: {
    count: 0,
    imageUrl: app.config.adminImageUrl
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
  getlist: function (pullDown = false) {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'red_packet/get_red_rule.php',
      method: 'POST',
      data: {
        'activity_id': app.activityId,
      },
      success: successUserlist,
      fail: showNetworkError
    });
    function successUserlist(res) {
      console.log(res.data.data.red_packet_rule_image);
      if (res.data.result != 0) {
        wx.showToast({
          title: res.data.message,
          icon: 'none'
        })
      } else {
        pages.setData({
          ruleimg: res.data.data.red_packet_rule_image.replace(/\\/, '/'),
          count: res.data.data.max_red_packet          
        })
        if (pullDown) {
          wx.stopPullDownRefresh();
        }
      }
    }
  },

  /**
   * 红包金额
   */
  domoney: function (options) {
    this.setData({
      count: options.detail.value
    });
  },

  //执行提交
  doSubmit: function (event) {
    var pages = this;
    var datas = {};
    datas.amount = pages.data.count;;
    datas.session_id = app.sessionId;
    datas.activity_id = app.activityId;
    datas.sign_id = pages.data.id;

    wx.request({
      url: getApp().config.apiUrl + 'red_packet/do_red_packet.php',
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
        wx.requestPayment({
          'timeStamp': res.data.data.timeStamp + "",
          'nonceStr': res.data.data.nonceStr,
          'package': res.data.data.package,
          'signType': res.data.data.signType,
          'paySign': res.data.data.paySign,
          'success': function (res) {
            wx.showToast({
              title: '支付成功',
              icon: 'none',
              success: setTimeout(function () {
                wx.reLaunch({
                  url: '/pages/detail/index?id=' + pages.data.id
                })
              }, 1500)
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