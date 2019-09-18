const app = getApp();
var inputVal = '';
var windowWidth = wx.getSystemInfoSync().windowWidth;
let windowHeight = wx.getSystemInfoSync().windowHeight;
var keyHeight = 0;
let socketOpen = false;
const socketMsgQueue = [];
var common = require('../../js/common.js');
var showNetworkError = common.showNetworkError;
const SocketTask = app.globalData.SocketTask;
const recorderManager = wx.getRecorderManager();
var innerAudioContext;
var wrapHeight = 0;
/**
 * 初始化数据
 */
function initData(that) {
  inputVal = '';
  that.setData({
    inputVal
  })
}

Page({
  /**
   * 页面的初始数据
   */
  data: {
    msgList: [],
    title: '',
    scrollHeight: 0,
    inputBottom: 0,
    voiceView: false,
    showButton: false,
    showvan: true,
    showView:false,
    scrollTop: 0,
    appearView:false,
    soundView:false,
    keybordView:false,
    imgurl: app.config.imageUrl,
    audiourl: app.config.audioUrl,
    navs: [
      { navimg: '../../images/photo.png', navtext: '相册' },
    ],
    navss: [
      { navimg: '../../images/photo.png', navtext: '相册' },
      { navimg: '../../images/photo.png', navtext: '相册' },
    ],
    imgArr: [
      //'http://bpic.588ku.com/element_origin_min_pic/16/10/30/528aa13209e86d5d9839890967a6b9c1.jpg',
      // 'http://bpic.588ku.com/element_origin_min_pic/16/10/30/54fcef525fa8f6037d180f3c26f3be65.jpg',
      // 'http://bpic.588ku.com/element_origin_min_pic/16/10/30/62e3ca3a02dddb002eff00482078d194.jpg',
      // 'http://bpic.588ku.com/element_origin_min_pic/16/10/31/c7167fcfb4ebcd12621c05b0c852e98e.jpg'
    ],
    indicatorDots: true,
    autoplay: true,
    interval: 5000,
    duration: 500,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    //获取页面高度
    var page = this;
    var query = wx.createSelectorQuery();
    query.select('#wrap').boundingClientRect()
    query.exec(function (res) {
      //console.log(res);
      wrapHeight = res[0].height;
      page.setData({
        'scrollHeight': wrapHeight - 75
      });
    })
    
    if (options.scene) {   
      var scene = Base64.decode(decodeURIComponent(options.scene));
      //console.log(scene);
      //二维码分享入口
      var gid = scene.split('_')[0];
      //app.companyId = sceneCompany.replace('"', "");
      var uid = parseInt(scene.split('_')[1]);
      if (uid) {
        setTimeout(function () {
          page.onCouponc(uid)
        }, 1500);
      }
    } else {
      //正常及转发入口
      if (options.gid) {
        page.setData({
          gid: options.gid,
          windowHeight: wrapHeight - 75
        });

        initData(page);
        //console.log(app.grouplist);
        if (app.grouplist[options.gid]) {
          var length = app.grouplist[options.gid].length;
        } else {
          var length = 1;
        }
        if (app.grouplist[options.gid]) {
          for (var i = 0; i < app.grouplist[options.gid].length; i++) {
            if (app.grouplist[options.gid][i]['type'] == 'audio') {
              //左
              if (app.grouplist[options.gid][i]['speaker'] == 'server') {
                app.grouplist[options.gid][i]['vids'] = '../../images/left_voice1.png';
              }
              //右
              if (app.grouplist[options.gid][i]['speaker'] == 'customer') {
                app.grouplist[options.gid][i]['vids'] = '../../images/right_voice1.png';
              }
            }
          }
        }
        page.setData({
          msgList: app.grouplist[options.gid],
          scrollTop: length * 100,
        });
        console.log(page.data.msgList);
      }
    }
    app.SocketTask.onMessage(data => {
      if (app.grouplist[page.data.gid]) {
        var length = app.grouplist[page.data.gid].length;
      } else {
        var length = 1;
      }
      page.setData({
        msgList: app.grouplist[page.data.gid],
        scrollTop: length * 100,
      }); 
    })
    innerAudioContext = wx.createInnerAudioContext();
    innerAudioContext.onPlay(() => {
      page.setData({
        status: 1,
        // show: true
      })
    })
    innerAudioContext.onStop(() => {
      page.setData({
        status: '',
        num: 0
      })
    })
    innerAudioContext.onEnded((e) => {
      var msg = page.data.msgList;
      if (msg[page.data.vids]['speaker'] == 'server') {
        msg[page.data.vids]['vids'] = '../../images/left_voice1.png';
      } else {
        msg[page.data.vids]['vids'] = '../../images/right_voice1.png';
      }
      
      page.setData({
        msgList: msg
      })
      page.setData({
        status: '',
        num: 100,
        voiceView: false,
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

  sendconfirm: function (e) {
    var page = this;
    page.sendmessages({ type: 'tap' });
  },

  sendmessages: function (e) {
    var page = this;
    //console.log(e.type);
    if (e.type == 'tap') {
      var types = 'text';
      var title = page.data.title;
    }
    if (e.type == 'image') {
      var types = 'image'
      var title = page.data.images;
    }
    if (e.type == 'audio') {
      var types = 'audio'
      var title = page.data.fileaudio;
    }
    app.SocketTask.send({
      data: JSON.stringify({
        type: types,
        group: page.data.gid,
        data: title,
        session_id: app.sessionId
      }),
      success: (res => {
        console.log('chenggong')
        page.setData({
          title: '',
          showButton: false,
          showvan: true,
          inputVal: ''
        });
      })
    })
  },
  previewImg: function (e) {
    //console.log(e.currentTarget.dataset.index);
    var index = e.currentTarget.dataset.index;
    var imgArr = [];
    imgArr.push(e.currentTarget.dataset.src);
    wx.previewImage({
      current: e.currentTarget.dataset.src,     //当前图片地址
      urls: imgArr,               //所有要预览的图片的地址集合 数组形式
      success: function (res) { },
      fail: function (res) { },
      complete: function (res) { },
    })
  },

  beginVoice(e) {
    var that = this;
    console.log(that.data.voiceView);
    if (that.data.voiceView) {
      that.startStop();
      if (e.currentTarget.dataset.index == that.data.vids) {
        var keys = e.currentTarget.dataset.index
      } else {
        var keys = that.data.vids
      }
      var msg = that.data.msgList;
      if (msg[keys]['speaker'] == 'server') {
        msg[keys]['vids'] = '../../images/left_voice1.png';
      } else {
        msg[keys]['vids'] = '../../images/right_voice1.png';
      }
      // msg[keys]['vids'] = '../../images/right_voice1.png';
      that.setData({
        msgList: msg
      })
    } else {
      var msg = that.data.msgList;
      if (msg[e.currentTarget.dataset.index]['speaker'] == 'server') {
        msg[e.currentTarget.dataset.index]['vids'] = '../../images/left_voice.gif';
      } else {
        msg[e.currentTarget.dataset.index]['vids'] = '../../images/right_voice.gif';
      }
      // msg[e.currentTarget.dataset.index]['vids'] = '../../images/right_voice.gif';
      that.setData({
        msgList: msg
      })
      that.startPlay(e.currentTarget.dataset.url);
    }
    that.setData({
      vids: e.currentTarget.dataset.index
    })
  },
  
  soundButton(){
    var that=this;
    that.setData({
      soundView:(!that.data.soundView),
      keybordView:true
    })
  },
  // addList() {
  //   var that = this;
  //   that.setData({
  //     showView: (!that.data.showView),
  //     appearView: false
  //   })
  // },
  keybordButton() {
    var that = this;
    that.setData({
      soundView: (!that.data.soundView),
      keybordView: false
    })
  },
  setPage(){
    wx.navigateTo({
      url: '/pages/chat/chat?gid=' + this.data.gid,
    })
  },
  turnFriend(){
    wx.navigateTo({
      url:'/pages/friend/friend'
    })
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
        url: getApp().config.apiUrl + "group/upload_image",
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
          })
          //console.log(page.data.imgurl + data.data);
          page.sendmessages({type:'image'});
        }
        if (fieldType == 'gallery') {
          galleryList.push(data.data);
          page.setData({
            galleryList: galleryList,
            galleryTitle: '重新选择',
          })
        }
      }

    }
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {
    var page = this;
    
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {

  },

  /**
   * 获取聚焦
   */
  focus: function(e) {
    var page = this;
    if (app.grouplist[page.data.gid]) {
      var length = app.grouplist[page.data.gid].length;
    } else {
      var length = 1;
    }
    keyHeight = e.detail.height;
    console.log(keyHeight);
    page.setData({
      scrollHeight: (wrapHeight - keyHeight - 74)
    });
    if (page.data.msgList) {
      var lenth = page.data.msgList.length - 1
    } else {
      var lenth = 0
    }
    page.setData({
      toView: 'msg-' + lenth,
      inputBottom: keyHeight + 'px',
      scrollTop: length * 100
    });
    //计算msg高度
    // calScrollHeight(this, keyHeight);
   
    page.setData({
      showView:false,
      appearView:false
    })
  },
  
  //失去聚焦(软键盘消失)
  blur: function(e) {
    var page = this;
    page.setData({
      scrollHeight: wrapHeight - 75,
      inputBottom: 0
    })
    page.setData({
      toView: 'msg-' + (page.data.msgList.length - 1)
    })
  },

  /**
   * 发送点击监听
   */
  sendClick: function(e) {
    if (e.detail.value) {
      this.setData({
        showButton: true,
        showvan: false
      })
    } else {
      this.setData({
        showButton: false,
        showvan: true
      })
    }
    this.setData({
      title: e.detail.value,
    });
  },

  /**
   * 开始录音
   */
  startManager: function () {
    //console.log(1222);
    var page = this;
    page.startStop();
    var msg = page.data.msgList;

    if (page.data.status == 1) {

      if (msg[page.data.vids]['speaker'] == 'server') {
        msg[page.data.vids]['vids'] = '../../images/left_voice1.png';
      } else {
        msg[page.data.vids]['vids'] = '../../images/right_voice1.png';
      }
      page.setData({
        msgList: msg
      })
    }

    var options = {
      duration: 60000,
      sampleRate: 44100,
      numberOfChannels: 2,
      encodeBitRate: 192000,
      format: 'mp3'
    }
    recorderManager.start(options);
  },

  /**
   * 结束录音
   */
  stopManager: function () {
    //console.log(22222222222);
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
      // page.setData({
      //   playurl: res.tempFilePath,
      //   audiolen: parseFloat(res.duration / 1000)
      // })
      // innerAudioContext.src = page.data.playurl;
      page.onUploadlu(res.tempFilePath, parseFloat(res.duration / 1000));
    })
    recorderManager.onError((res) => {
      //console.log(res);
      // wx.showToast({
      //   title: '录音错误',
      //   duration: 2000,
      //   icon: 'none'
      // })
      return;
    })
  },

  /**
   * 播放录音
   */
  startPlay: function (url) {
    innerAudioContext.src = this.data.audiourl + url;
    //console.log(innerAudioContext.src);
    var page = this;
    page.setData({
      num: 0,
      voiceView: true,
    })
    if (!url) {
      wx.showToast({
        title: '播放错误',
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
    page.setData({
      voiceView: false,
    })
    innerAudioContext.stop();
  },

  /**
   * 上传录音
   */
  onUploadlu: function (audio, durat) {
    var page = this;
    if (!audio) {
      wx.showToast({
        title: '请录制语音后再进行提交',
        duration: 2000,
        icon: 'none'
      })
      page.setData({
        bao: true
      })
      return;
    }
    wx.uploadFile({
      url: app.config.apiUrl + 'group/upload',
      filePath: audio,
      name: 'audio',
      success: res => {
        successUploadVideo(res, durat);
      }
    });
    /**
     * 成功上传视频
     */
    function successUploadVideo(res, durat) {
      res.data = JSON.parse(res.data);
      //console.log(res);
      if (res.data.result != 0) {
        wx.showToast({
          title: res.data.message
        })
        page.setData({
          bao: true
        })
      } else {
        //res.data.data为视频相对路径
        page.setData({
          fileaudio: res.data.url + '_' + durat,
        });
        //console.log(res.data.url + '_' + durat);
        page.sendmessages({ type: 'audio' });
      }
    }
  },

  turnFriend: function (e) {
    if (app.userInfo.id != e.currentTarget.dataset.user) {
      var page = this;
      wx.navigateTo({
        url: '/pages/friend/friend?uid=' + e.currentTarget.dataset.user + '&gid=' + page.data.gid,
      })
    }
  },
  
  /**
   * 退回上一页
   */
  toBackClick: function() {
    wx.navigateBack({})
  },

})