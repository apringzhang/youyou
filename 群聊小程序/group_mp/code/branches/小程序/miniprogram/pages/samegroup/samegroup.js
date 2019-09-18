const app = getApp();
var common = require('../../js/common.js');
var showNetworkError = common.showNetworkError;
//获取应用实例
import Dialog from '../../dist/dialog/dialog';

Page({

  /**
   * 页面的初始数据
   */
  data: {
    imgurl: app.config.imageUrl,
    val: '',
    show:false,
    hidden:false,
    showView: false,
    alertView: false,
    inputShowed: false,
  },
  
  showButton: function () {
    var that = this;
    that.setData({
      showView: (!that.data.showView),
      inputShowed: (!that.data.inputShowed),
    })
  },
  click: function () {
    wx.navigateTo({
      url: '/pages/chat/chat',
    })
  },
  clickme: function (e) {
    wx.navigateTo({
      url: '/pages/contact/contact?gid=' + e.currentTarget.dataset.id,
    })
  },
  goBack: function () {
    var that = this;
    that.setData({
      showView: (!that.data.showView),
      inputShowed: (!that.data.inputShowed),
    })
  },
  openConfirm: function () {
    wx.showModal({
        title: '创建超级群',
        content: '群介绍和群名称',
        confirmText: "确定",
        cancelText: "取消",
        success: function (res) {
            console.log(res);
            if (res.confirm) {
                console.log('用户点击主操作')
            }else{
                console.log('用户点击辅助操作')
            }
        }
    });
},
  /**
* 生命周期函数--监听页面加载
*/
  onLoad: function (options) {
    this.setData({
      uid: options.uid,
    });
    this.getGouplist();
  },

  //获取共同群
  getGouplist: function (pullDown = false) {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'group/and_group',
      method: 'POST',
      data: {
        session_id: app.sessionId,
        fuid: pages.data.uid
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
          group: res.data.data,
          len: res.data.data.length
        })
        if (pullDown) {
          wx.stopPullDownRefresh();
        }
      }
    }
  },

  //获取群列表搜索
  bindlike: function (e) {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'group/and_group',
      method: 'POST',
      data: {
        session_id: app.sessionId,
        fuid: pages.data.uid,
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
        pages.setData({
          group: res.data.data,
          len: res.data.data.length
        })
      }
    }
  },
  
  bindvalue: function (e) {
    this.setData({
      val: e.detail.value
    })
  },

  bindtitle: function () {
    var page = this;
    page.bindlike({ detail: { value: page.data.val } });
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
