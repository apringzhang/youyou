const app = getApp();
var common = require('../../js/common.js');
var showNetworkError = common.showNetworkError;

Page({

/**
 * 页面的初始数据
 */
data: {
  scroll:'', //滚动到指定 id值的子元素
  hiddenn: true,//hint_box 提示框 展示隐藏
  nav_text: '',//hint_box 提示框里面的文本
},
/**
* 生命周期函数--监听页面加载
*/
  onLoad: function (options) {
    this.setData({
      gid: options.gid,
    });
    this.getGouplist();
  },

  //获取群成员列表(除管理员)
  getGouplist: function (pullDown = false) {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'group/groups_admtouser',
      method: 'POST',
      data: {
        g_id: pages.data.gid
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
          items: res.data.data,
        })
        if (pullDown) {
          wx.stopPullDownRefresh();
        }
      }
    }
  },

  bindchange: function (e) {
    console.log(e);
    console.log(this.data.items);
    
    this.setData({
      checkvalues: e.detail.value,
    })
  },

  bindzhuan: function () {
    if (!this.data.checkvalues) {
      wx.showToast({
        title: '请选择要转让的成员',
        icon: 'none'
      })
      return;
    }
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'group/transfer_group',
      method: 'POST',
      data: {
        g_id: pages.data.gid,
        uid: pages.data.checkvalues,
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
        wx.showToast({
          title: '转让成功',
          duration: 1500,
          success: setTimeout(function () {
            wx.navigateBack({
              delta: 1
            })
          }, 1500)
        })
      }
    }
  },

  //列表搜索
  bindlike: function (e) {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'group/groups_admtwo',
      method: 'POST',
      data: {
        g_id: pages.data.gid,
        like: e.detail.value
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
        var resslt = res.data.data;
        var arr = [];
        for (var i = 0; i < resslt.length; i++) {
          if (resslt[i]['g_userid'] == pages.data.checkvalues) {
            arr = resslt[i];
            arr['checks'] = true;
            resslt[i] = arr;
          }
        }
        
        console.log(pages.data.checkvalues);
        pages.setData({
          items: resslt
        })
      }
    }
  },

  bindtitle: function () {
    var page = this;
    page.bindlike({ detail: {value: page.data.val}});
  },

  bindvalue: function (e) {
    this.setData({
      val: e.detail.value
    })
  },

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
  })