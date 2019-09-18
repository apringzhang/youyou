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
      // console.log(res);
      if (res.data.result != 0) {
        wx.showToast({
          title: res.data.message,
          icon: 'none'
        })
      } else {
        pages.setData({
          items: res.data.data,
        })
        console.log(pages.data.items);
        if (pullDown) {
          wx.stopPullDownRefresh();
        }
      }
    }
  },

  checkboxChange: function(e) {
    console.log(e);
    this.setData({
      checkvalues: e.detail.value
    })
  },

  binddelete: function () {
    if (!this.data.checkvalues) {
      wx.showToast({
        title: '请选择要删除的成员',
        icon: 'none'
      })
      return;
    }
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'group/delete_groups_touser',
      method: 'POST',
      data: {
        g_id: pages.data.gid,
        uid: pages.data.checkvalues
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
          title: '删除成功',
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