// pages/detail/index.js
const app = getApp();
var innerAudioContext;
var common = require('../../js/common.js');
var showNetworkError = common.showNetworkError;
Page({

  /**
   * 页面的初始数据
   */
  data: {
    page: 1,
    id: 0,
    isShow: 'none',
    qrcode: '',
    status: '',
    audiourl: app.config.audioUrl,
    num: 0
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (param) {
    var pages = this;
    innerAudioContext = wx.createInnerAudioContext();
    innerAudioContext.onPlay(() => {
      pages.setData({
        status: 1,
        show: true
      })
    })
    innerAudioContext.onStop(() => {
      pages.setData({
        status: '',
        num: 0,
        show: false
      })
    })
    innerAudioContext.onEnded(() => {
      pages.setData({
        status: '',
        num: 100,
        show: false
      })
    })

    innerAudioContext.onTimeUpdate(() => {
      //console.log(innerAudioContext.currentTime);
      var numb = parseFloat(innerAudioContext.currentTime) / parseFloat(this.data.audiolen) * 100;
      pages.setData({
        num: numb
      })
    })
    if (param.scene) {
      var scene = decodeURIComponent(param.scene);
      //二维码分享入口
      var sceneActivityId = scene.split('-')[0];
      var sceneId = scene.split('-')[1];
      pages.setData({
        id: sceneId
      })
      app.activityId = sceneActivityId;
      wx.setStorageSync("activityId", sceneActivityId);
      this.loadReady(this.data.id);
      return;
    } else {
      //正常及转发入口
      common.share(param, function () {
        common.init(pages, function (info) {

        });
      });
    }
    var id = "";
    if (param.id != "") {
      id = param.id;
      this.setData({
        id:id
      });
    } else {
      id = this.data.id;
    }
  },

  /**
   * 获取数据详情
   */
  loadReady: function(id, pullDown=false) {
    var pages = this; 
    wx.request({
      url: getApp().config.apiUrl + 'apply/get_apply.php',
      method: 'POST',
      data: {
        'session_id': getApp().sessionId,
        'appid': getApp().appid,
        'id': id,
        'activity_id': app.activityId
      },
      success: successDodetail,
      fail: showNetworkError
    });
    function successDodetail(res) {
      res.data.data.sign_introduce_image = JSON.parse(res.data.data.sign_introduce_image);
      if (res.data.result != 0) {
        wx.showToast({
          title: res.data.message,
          icon: 'none'
        })
      } else {
        pages.setData({
          rel: res.data.data,
          videoUrl: app.config.videoUrl,
          imageUrl: app.config.imageUrl,
          playurl: app.config.audioUrl + res.data.data.sign_audio,
          audiolen: res.data.data.sign_duration,
        })

        innerAudioContext.src = pages.data.playurl;
        if (pullDown) {
          wx.stopPullDownRefresh();
        }
      }
    }
  },

  /**
   * 播放录音
   */
  startPlay: function () {
    var page = this;
    page.setData({
      num: 0
    })
    // if (!page.data.playurl) {
    //   wx.showToast({
    //     title: '请录制语音后播放',
    //     duration: 2000,
    //     icon: 'none'
    //   })
    //   return;
    // }
    innerAudioContext.play();
  },

  /**
   * 停止
   */
  startStop: function () {
    var page = this;
    innerAudioContext.stop();
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
    if (this.data.id) {
      this.loadReady(this.data.id);
    }

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
    innerAudioContext.destroy();
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {
    this.loadReady(this.data.id, true);
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    var pages = this;
    this.setData({
      page : this.data.page + 1,
    })
    wx.showLoading({
      title: '加载中'
    })
    wx.request({
      url: getApp().config.apiUrl + 'apply/get_apply_gift.php',
      method: 'POST',
      data: {
        'session_id': getApp().sessionId,
        'appid': getApp().appid,
        'id': this.data.id,
        'page': pages.data.page,
        'activity_id': app.activityId
      },
      success: successDolist,
      fail: showNetworkError
    });
    function successDolist(res) {
      wx.hideLoading();
      //res.data.data.sign_introduce_image = JSON.parse(res.data.data.sign_introduce_image);
      if (res.data.result != 0) {
        wx.showToast({
          title: res.data.message,
          icon: 'none'
        })
      } else {
        var result = pages.data.rel.gift_list;
        result = result.concat(res.data.data);
        pages.data.rel.gift_list = result;
        pages.setData({
          rel: pages.data.rel
        })
      }
    }
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
    return {
      path: '/pages/detail/index?activity_id=' + app.activityId + "&id=" + this.data.id
    }
  },

  /**
   * 投票
   */
  doActivity: function() {
    var page = this;
    wx.request({
      url: getApp().config.apiUrl + 'vote/do_vote.php',
      method: 'POST',
      data: {
        'session_id': getApp().sessionId,
        'appid': getApp().appid,
        'sign_id': page.data.id,
        'activity_id': app.activityId
      },
      success: successActivity,
      fail: showNetworkError
    });
    function successActivity(res) {
      if (res.data.result != 0) {
        wx.showToast({
          title: res.data.message,
          icon: 'none'
        })
      } else {
        wx.showToast({
          title: '投票成功',
        })
        page.loadReady(page.data.id);
      }
    }
  },

  /**
   * 返回首页
   */
  backIndex: function() {
    wx.switchTab({
      url: '../index/index'
    })
  },

  showLayer: function() {
    var page = this;
    this.setData({
      isShow:'block',
      shareImageUrl: getApp().config.apiUrl + 'apply/get_share_image.php?id='
      + page.data.id + '&appid=' + app.appid + '&activity_id=' + app.activityId
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
      url: getApp().config.apiUrl + 'apply/get_share_image.php?id='
      + page.data.id + '&appid=' + app.appid + '&activity_id=' + app.activityId,
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