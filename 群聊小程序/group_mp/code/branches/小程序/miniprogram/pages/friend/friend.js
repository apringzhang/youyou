const app = getApp();
var common = require('../../js/common.js');
var showNetworkError = common.showNetworkError;

// pages/friend/friend.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
    checked: false,
  },

  goGroup:function(){
    var page = this;
    wx.navigateTo({
      url: '/pages/samegroup/samegroup?uid=' + page.data.uid,
    })
  },
  goRemark:function(){
    wx.navigateTo({
      url: '/pages/groupremark/groupremark',
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.setData({
      uid: options.uid,
      gid: options.gid
    });
  },

  //获取群成员信息
  getGouplist: function (pullDown = false) {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'group/info_group',
      method: 'POST',
      data: {
        g_id: pages.data.gid,
        uid: pages.data.uid
      },
      success: successGouplist,
      fail: showNetworkError
    });
    function successGouplist(res) {
      // console.log(res);
      if (res.data.result != 0) {
        wx.showToast({
          title: res.data.message,
          icon: 'none'
        })
      } else {
        pages.setData({
          info: res.data.data,
        })
        if (pullDown) {
          wx.stopPullDownRefresh();
        }
      }
    }
  },

  onChange(event) {
    var pages = this;
    // 需要手动对 checked 状态进行更新
    if (event.detail == true) {
      pages.collection();
    } else {
      pages.delcollection();
    }
  },

  collection: function (event) {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'message/collection',
      method: 'POST',
      data: {
        session_id: app.sessionId,
        groups_id: pages.data.gid,
        touid: pages.data.uid
      },
      success: successGouplist,
      fail: showNetworkError
    });
    function successGouplist(res) {
      // console.log(res);
      if (res.data.result != 0) {
        wx.showToast({
          title: res.data.message,
          icon: 'none'
        })
      } else {
        pages.setData({
          checked: true
        })
      }
    }
  },

  delcollection: function (event) {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'message/del_collection',
      method: 'POST',
      data: {
        session_id: app.sessionId,
        gid: pages.data.gid,
        touid: pages.data.uid
      },
      success: successGouplist,
      fail: showNetworkError
    });
    function successGouplist(res) {
      // console.log(res);
      if (res.data.result != 0) {
        wx.showToast({
          title: res.data.message,
          icon: 'none'
        })
      } else {
        pages.setData({
          checked: false
        })
      }
    }
  },

  collyn: function () {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'message/collection_yn',
      method: 'POST',
      data: {
        session_id: app.sessionId,
        gid: pages.data.gid,
        touid: pages.data.uid
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
        if (res.data.data == 1) {
          pages.setData({
            checked: true,
          })
        } else {
          pages.setData({
            checked: false,
          })
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
  onShow: function (options) {
    console.log(options);
    this.getGouplist();
    this.collyn();
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