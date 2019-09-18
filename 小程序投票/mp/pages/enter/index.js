// pages/enter/index.js
const app = getApp();
const recorderManager = wx.getRecorderManager();
var innerAudioContext;
var common = require('../../js/common.js');
var showNetworkError = common.showNetworkError;
Page({
  /*
   * 页面的初始数据
   */
  data: {
    //封面图
    cover: "",
    imageUrl: app.config.imageUrl,
    galleryList: [],
    galleryTitle: '选择图片',
    videoTitle: '选择视频',
    sex: [{ value: "男", checked: "true", name: "0" }, { value: "女", name: "1" }],
    status: '',
    num: 0,
    bao: true,
    recordText: '点击录音',
    recordStatus: 0 //0未开始录音 1已开始录音
  },

  //执行提交
  doSubmit: function (event) {
    if (this.data.bao) {
      this.setData({
        bao: false
      })
      this.onUploadlu(event);
    }
  },

  //提交
  Submitclick: function (event) {
    var page = this;
    var datas = {};
    var param = event.detail.value;
    datas.sex = param.sex;
    datas.username = param.username;
    datas.sign_unit = param.signUnit;
    datas.sign_class = param.signClass;
    datas.mobile = param.mobile;
    datas.sign_declaration = param.signDeclaration;
    datas.sign_introduce = param.signIntroduce;
    datas.session_id = app.sessionId;
    datas.activity_id = app.activityId;
    datas.appid = app.appid;
    datas.sign_image = this.data.cover;
    datas.sign_introduce_image = JSON.stringify(this.data.galleryList);
    datas.sign_video = this.data.fileVideo;
    datas.sign_audio = this.data.fileaudio;
    datas.sign_duration = this.data.audiolen
    wx.request({
      url: getApp().config.apiUrl + 'apply/do_apply.php',
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
        wx.showToast({
          title: '报名成功',
          success: setTimeout(function () {
            wx.reLaunch({
              url: '/pages/index/index'
            })
          }, 1500)
        })
      }
    }
  },

  /**
   * 上传录音
   */
  onUploadlu: function (e) {
    var page = this;
    if (!page.data.playurl) {
      wx.showToast({
        title: '请录制语音后再进行提交',
        duration: 2000,
        icon: 'none'
      })
      return;
    }
    wx.uploadFile({
      url: app.config.apiUrl + 'upload/audio.php',
      filePath: page.data.playurl,
      name: 'audio',
      success: res => {
        successUploadVideo(res);
      }
    });
    /**
     * 成功上传视频
     */
    function successUploadVideo(res) {
      res.data = JSON.parse(res.data);
      if (res.data.result != 0) {
        wx.showToast({
          title: res.data.message
        })
      } else {
        //res.data.data为视频相对路径
        page.setData({
          fileaudio: res.data.data,
        });
        page.Submitclick(e);
      }
    }
  },

  onClickUpload: function (event) {
    var page = this;
    var count = 0;
    var fieldType = event.target.dataset.fieldType;
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
        url: getApp().config.apiUrl + "upload/image.php", //仅为示例，非真实的接口地址
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
      if (data.result != 0) {
        wx.showToast({
          title: data.message,
        });
      }
      if (data.result == 0) {
        if (fieldType == 'cover') {
          page.setData({
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
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var page = this;
    page.setData({
      bao: true
    })
    common.share(options, function () {
      common.init(page, function (info) {

      });
    });
    innerAudioContext = wx.createInnerAudioContext();
    innerAudioContext.onPlay(() => {
      page.setData({
        status: 1,
        show: true
      })
    })
    innerAudioContext.onStop(() => {
      page.setData({
        status: '',
        num: 0
      })
    })
    innerAudioContext.onEnded(() => {
      page.setData({
        status: '',
        num: 100
      })
    })

    innerAudioContext.onTimeUpdate(() => {
      //console.log(innerAudioContext.currentTime);
      var num = parseFloat(innerAudioContext.currentTime) / parseFloat(this.data.audiolen) * 100;
      page.setData({
        num: num
      })
    })
  },
  /**
   * 上传视频
   */
  onUploadVideo: function (e) {
    var page = this;
    wx.chooseVideo({
      compressed: true,
      maxDuration: 60,
      success: successChooseVideo
    });
    /**
     * 成功选择视频
     */
    function successChooseVideo(res) {
      //视频临时文件地址
      var tempFilePaths = res.tempFilePath;
      //视频临时缩略图地址
      var thumbTempFilePath = res.thumbTempFilePath;
      //显示Loading覆盖层
      wx.showLoading({
        title: '上传中',
        mask: true
      })
      wx.uploadFile({
        url: app.config.apiUrl + 'upload/video.php',
        filePath: tempFilePaths,
        name: 'video',
        success: res => {
          successUploadVideo(res, thumbTempFilePath);
        }
      });
    }
    /**
     * 成功上传视频
     */
    function successUploadVideo(res, thum) {
      //隐藏Loading覆盖层
      wx.hideLoading();
      res.data = JSON.parse(res.data);
      if (res.data.result != 0) {
        wx.showToast({
          title: res.data.message
        })
      } else {
        //res.data.data为视频相对路径
        page.setData({
          videoPath: thum,
          videoTitle: '重新选择',
          fileVideo: res.data.data,
        });
      }
    }
  },
  //录音按钮
  onRecordButtonClick: function () {
    if (this.data.recordStatus == 0) {
      this.startManager();
      this.setData({
        recordText: '正在录音',
        recordStatus: 1
      });
    } else if (this.data.recordStatus == 1) {
      this.stopManager();
      this.setData({
        recordText: '录音成功',
        recordStatus: 0
      });
    }
  },

  /**
   * 开始录音
   */
  startManager: function () {
    var options = {
      duration: 600000,
      sampleRate: 22050,
      numberOfChannels: 1,
      encodeBitRate: 32000,
      format: 'mp3'
    }
    recorderManager.start(options);
  },

  /**
   * 结束录音
   */
  stopManager: function () {
    var page = this;
    recorderManager.stop();
    recorderManager.onStop((res) => {
      if (res.duration < 300) {
        wx.showToast({
          title: '语音太短',
          duration: 2000,
          icon: 'none'
        })
        return;
      }
      console.log(res.tempFilePath);
      page.setData({
        playurl: res.tempFilePath,
        audiolen: parseFloat(res.duration / 1000),
        show: true
      })
      innerAudioContext.src = page.data.playurl;
    })
    recorderManager.onError((res) => {
      wx.showToast({
        title: '录音错误',
        duration: 2000,
        icon: 'none'
      })
      return;
    })
  },

  /**
   * 播放录音
   */
  startPlay: function () {
    if (this.data.recordStatus == 1) {
      return;
    }
    var page = this;
    page.setData({
      num: 0
    })
    if (!page.data.playurl) {
      wx.showToast({
        title: '请录制语音后播放',
        duration: 2000,
        icon: 'none'
      })
      return;
    }
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
      path: '/pages/enter/index?activity_id=' + app.activityId
    }
  }
})
