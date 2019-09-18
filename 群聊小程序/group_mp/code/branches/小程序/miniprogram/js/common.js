var app = getApp();
/**
 * 页面初始化
 * @param page 当前页面对象
 * @param callback 回调函数
 */
function init(page, callback) {
  topRollText(page);
  getActivityInfo(function (info) {
    //根据报名和活动时间判断活动状态及倒计时
    var activity_status = 0;
    if (info.now_timestamp > info.apply_start_timestamp && info.now_timestamp < info.apply_stop_timestamp) {
      countDown(page, info.now_timestamp, info.apply_stop_timestamp);
      activity_status = 1;
    } else if (info.now_timestamp > info.start_timestamp && info.now_timestamp < info.stop_timestamp) {
      countDown(page, info.now_timestamp, info.stop_timestamp);
      activity_status = 2;
    }
    page.setData({
      activity_status: activity_status
    });
    page.setData({
      activity_notice: info.activity_notice
    });
    callback(info);
  });
  //广告轮播图
  getAdList(function (adInfo) {
    page.setData({
      slideshow: adInfo
    });
  });
}
/**
 * 分享
 */
function share(options, callback) {
  var activityId = options.activity_id;
  if (activityId) {
    wx.setStorageSync("activityId", activityId);
    app.activityId = activityId;
  }
  callback();
}
/**
 * 滚动公告
 */
//计时器
function topRollText(page) {
  //设置初始滚动值
  page.setData({
    scrollTextLeft: 10,
    topRollTextInterval: false
  });
  var windowWidth = wx.getSystemInfoSync().windowWidth;
  if (!page.data.topRollTextInterval) {
    var topRollTextInterval = setInterval(function () {
      var left = page.data.scrollTextLeft;
      if (-left >= windowWidth) {
        left = windowWidth;
        page.setData({
          scrollTextLeft: windowWidth
        });
      }
      page.setData({
        scrollTextLeft: left - 5
      });
    }, 150);
    page.setData({
      topRollTextInterval: topRollTextInterval
    })
  }
}
/**
 * 显示网络请求错误
 */
function showNetworkError() {
  wx.showToast({
    title: "网络错误"
  })
}

/**
 * 获取活动信息
 */
function getActivityInfo(callback) {
  wx.request({
    url: app.config.apiUrl + 'activity/get_activity.php',
    data: {
      session_id: app.sessionId,
      appid: app.appid,
      activity_id: app.activityId
    },
    method: 'POST',
    success: getActivityInfoSuccess
  });
  //成功获取活动信息
  function getActivityInfoSuccess(res) {
    if (res.data.result != 0) {
      callback(res.data.message);
    } else {
      callback(res.data.data);
    }
  }
}

/**
 * 获取广告
 */
function getAdList(callback) {
  wx.request({
    url: app.config.apiUrl + 'ad/get_ad_list.php',
    data: {
      session_id: app.sessionId,
      appid: app.appid,
      activity_id: app.activityId,
      adp_code: 'top_banner'
    },
    method: 'POST',
    success: getAdlistSuccess
  });
  function getAdlistSuccess(res) {
    if (res.data.result != 0) {
      callback(res.data.message);
    } else {
      callback(res.data.data);
    }
  }

}
/**
 * 倒计时
 * @param page 页面独享
 * @param endTime 结束时间戳
 */
//倒计时计时器
function countDown(page, nowTime, endTime) {
  //清除定时器
  if (page.data.countDownInterval) {
    clearInterval(page.data.countDownInterval);
  }
  page.setData({
    countDownInterval: false
  })
  //倒计时
  var countDownTime = parseInt(endTime) - parseInt(nowTime);
  if (countDownTime <= 0) {
    page.setData({
      countDownDay: 0,
      countDownHour: 0,
      countDownMinutes: 0,
      countDownSeconds: 0
    });
  } else if (!page.data.countDownInterval) {
    var countDownInterval = setInterval(function () {
      var countDownDay = Math.floor(countDownTime / (24 * 60 * 60));
      var countDownHour = Math.floor((countDownTime % (24 * 60 * 60)) / (60 * 60));
      var countDownMinutes = Math.floor((countDownTime % (24 * 60 * 60)) % (60 * 60) / 60);
      var countDownSeconds = Math.floor((countDownTime % (24 * 60 * 60)) % (60 * 60) % 60);
      page.setData({
        countDownDay: countDownDay,
        countDownHour: countDownHour,
        countDownMinutes: countDownMinutes,
        countDownSeconds: countDownSeconds
      });
      countDownTime -= 1;
    }, 1000);
    page.setData({
      countDownInterval: countDownInterval
    })
  }
}
function webSocket() {
  // 创建Socket
  var SocketTask = wx.connectSocket({
    url: 'wss://groupmp.honorsoftware.cn:8282',
    success: function (res) {
      console.log('WebSocket连接创建', res)
    },
    fail: function (err) {
      wx.showToast({
        title: '网络异常！',
      })
      console.log(err)
    },
  })
  return SocketTask;
}
module.exports.init = init;
exports.share = share;
exports.webSocket = webSocket;
exports.showNetworkError = showNetworkError;