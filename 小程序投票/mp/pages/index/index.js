//index.js
const common = require('../../js/common.js');
//获取应用实例
const app = getApp()

Page({
  data: {
    appId: app.appid,
    //列表数据
    voteList: [],
    //页数
    page: 1,
    //搜索条件
    keyword: '',
    imageUrl: app.config.imageUrl,
    adminImageUrl: app.config.adminImageUrl,
    isShow: 'none'
  },
  onLoad: function (param) {
    var page = this;
    if (param.scene) {
      var activityId = decodeURIComponent(param.scene);
      //二维码分享入口
      app.activityId = activityId;
      wx.setStorageSync("activityId", activityId);
    }
    var page = this;
    common.share(param, function () {
      common.init(page, function (info) {
        page.setData({
          apply_count: info.apply_count,
          total_count: info.total_count,
          visit_count: info.visit_count
        });
      });
    });
    this.loadData();
  },
  /**
   * 加载列表
   */
  loadData: function () {
    var page = this;
    
    wx.request({
      url: app.config.apiUrl + 'apply/get_apply_list.php',
      data: {
        activity_id: page.data.activity_id,
        session_id: app.sessionId,
        appid: app.appid,
        activity_id: app.activityId,
        keyword: page.data.keyword,
        page: 1,
        rank: 2
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
 * 页面相关事件处理函数--监听用户下拉动作
 */
  onPullDownRefresh: function () {
    this.setData({
      page: 1
    });
    var page = this;
    common.init(page, function (info) {
      page.setData({
        apply_count: info.apply_count,
        total_count: info.total_count,
        visit_count: info.visit_count
      });
    });
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
        activity_id: page.data.activity_id,
        page: page.data.page,
        session_id: app.sessionId,
        appid: app.appid,
        activity_id: app.activityId,
        keyword: page.data.keyword,
        rank: 2
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
      }
      wx.hideLoading();
    }
  },

  //绑定搜索回调
  doSearch: function (info) {
    if (info.detail.value.keyword == "") {
      wx.showToast({
        title: '搜索内容不能为空'
      })
    } else {
      wx.navigateTo({
        url: '/pages/search/search?keyword=' + info.detail.value.keyword
      })
    }
  },

  /**
   * 报名页
   */
  backEnter: function () {
    wx.navigateTo({
      url: '../enter/index'
    })
  },
  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function (res) {
    return {
      path: '/pages/index/index?activity_id=' + app.activityId
    }
  },
  showLayer: function () {
    var page = this;
    this.setData({
      isShow: 'block',
      shareImageUrl: getApp().config.apiUrl + 'apply/get_activity_share_image.php?appid=' + app.appid + '&activity_id=' + app.activityId
    })
  },
  hideLayer: function () {
    this.setData({
      isShow: 'none'
    })
  },
  //点击保存分享图片按钮
  saveShareImage: function () {
    var page = this;
    wx.downloadFile({
      url: getApp().config.apiUrl + 'apply/get_activity_share_image.php?appid=' + app.appid + '&activity_id=' + app.activityId,
      success: function (res) {
        if (res.statusCode === 200) {
          wx.saveImageToPhotosAlbum({
            filePath: res.tempFilePath
          });
        }
      }
    })
  }
})
