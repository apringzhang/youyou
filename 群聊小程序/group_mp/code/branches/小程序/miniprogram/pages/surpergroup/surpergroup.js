//index.js
var socketOpen = false;
import Dialog from '../../dist/dialog/dialog';
const app = getApp();
var common = require('../../js/common.js');
var showNetworkError = common.showNetworkError;
const SocketTask = app.globalData.SocketTask;

Page({

  /**
   * 页面的初始数据
   */
  data: {
    len: 0,
    lobby_flag: false,
    imgurl: app.config.imageUrl,
    show: false,
    hidden: false,
    showView: false,
    alertView: false,
    inputShowed: false,
    inputvalue:'',
    hiddenmodalput: true,
    images: '../../images/tjtp.png'
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

    this.setData({

      hiddenmodalput: !this.data.hiddenmodalput,
      
    })

  },

  bindname: function (e) {
    console.log(e);
    this.setData({
      gname: e.detail.value,
    });

  },

  bindgg: function (e) {
    this.setData({
      gg: e.detail.value,
    });
  },

  cancel: function () {

    this.setData({
      images: '../../images/添加图片.png',
      hiddenmodalput: true,
      inputvalue:'',
      cover: ''
    });

  },

  /**
   * 是否需要审核
   */
  lobby_flag: function (e) {
    console.log(e);
    var that = this;

    if (!e.target.dataset.checked) {
      that.data.lobby_flag = true
    } else {
      that.data.lobby_flag = false
    }
    console.log(that.data.lobby_flag);
    this.setData({
      lobby_flag: that.data.lobby_flag
    })
  },

  //确认

  confirm: function (e) {
    var pages = this;
    if (!pages.data.cover) {
      wx.showToast({
        title: '请选择群图标',
        icon: 'none'
      })
      return;
    }
    if (!pages.data.gname) {
      wx.showToast({
        title: '请填写群名称',
        icon: 'none'
      })
      return;
    }
    if (!pages.data.gg) {
      wx.showToast({
        title: '请填写群公告',
        icon: 'none'
      })
      return;
    }
    
    wx.request({
      url: getApp().config.apiUrl + 'group/add_group',
      method: 'POST',
      data: {
        name: pages.data.gname,
        sessionId: app.sessionId,
        icon: pages.data.cover,
        notice: pages.data.gg,
        isaudit: pages.data.lobby_flag
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
          title: '创建成功',
        })
        pages.setData({
          hiddenmodalput: true,
          images: '../../images/添加图片.png',
          cover: ''
        })
        pages.onShow();
      }
    }
    

  },
  /**
* 生命周期函数--监听页面加载
*/
  onLoad: function (options) {
    
  },

  //获取群列表
  getGouplist: function (pullDown = false) {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'group/group_list',
      method: 'POST',
      data: {
        sessionId: app.sessionId
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
      url: getApp().config.apiUrl + 'group/group_like',
      method: 'POST',
      data: {
        sessionId: app.sessionId,
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

  //上传图片
  onClickUpload: function (event) {
    var page = this;
    var count = 0;
    var fieldType = event.currentTarget.dataset.fieldtype;
    var i = 0;
    var galleryList = [];
    if (fieldType == 'cover') {
      count = 1;
    }
    if (fieldType == 'gallery') {
      count = 5;
    }

    wx.chooseImage({
      count: count, // 默认9
      sizeType: 'compressed',
      success: successImageUpload,
    });

    function successImageUpload(e) {
      wx.showLoading({
        title: "上传中"
      });
      // e.tempFilePaths.forEach(function (value, index, array) {
      wx.uploadFile({
        url: getApp().config.apiUrl + "group/upload_image", //仅为示例，非真实的接口地址
        filePath: e.tempFilePaths[i],
        name: 'image',
        formData: {
          'session_id': getApp().sessionId
        },
        success: res => {
          successUploadImage(res, e)
        }
      })
      // })
    };

    function successUploadImage(res, e) {
      wx.hideLoading();
      i++
      var data = JSON.parse(res.data);
      // console.log(data);
      if (data.result != 0) {
        wx.showToast({
          title: data.message,
        });
      }
      if (data.result == 0) {
        if (fieldType == 'cover') {
          page.setData({
            images: page.data.imgurl + data.data,
            cover: data.data,
          })
        }
        if (fieldType == 'gallery') {
          galleryList.push(data.data);
          page.setData({
            galleryList: galleryList,
            galleryTitle: '重新选择',
          })
        }
      }
      if (i < e.tempFilePaths.length) {
        successImageUpload(e);
      }
    }
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
   
    // SocketTask.onOpen(res => {
    //   socketOpen = true;
    //   console.log('监听 WebSocket 连接打开事件。', res)
    // })
    // SocketTask.onClose(onClose => {
    //   console.log('监听 WebSocket 连接关闭事件。', onClose)
    //   socketOpen = false;
    //   // common.webSocket()
    // })
    // SocketTask.onError(onError => {
    //   console.log('监听 WebSocket 错误。错误信息', onError)
    //   socketOpen = false
    // })
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    this.getGouplist();
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
    this.getGouplist(true);
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
