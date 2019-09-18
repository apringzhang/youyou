//index.js
const common = require('../../js/common.js');
//获取应用实例
const app = getApp()

Page({
  data: {
    //列表数据
    voteList: [],
    //页数
    page: 1,
    //搜索条件
    keyword: '',
    imageUrl: app.config.imageUrl
  },
  onLoad: function (param) {
    var page = this;
    //这是当前小程序的appid
    common.share(param, function () {
      common.init(page, function (info) {

      });
    });
    if (param.keyword != "") {
      this.setData({
        keyword: param.keyword
      });
    }
    this.loadData();
  },
  /**
   * 加载列表
   */
  loadData: function (param){
    var page = this;   
    wx.request({
      url: app.config.apiUrl + 'apply/get_apply_list.php',
      data: {
        session_id: app.sessionId,
        appid: app.appid,
        activity_id: app.activityId,
        keyword: page.data.keyword,
        page: 1,
        rank: ''
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
    })
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
        page: page.data.page,
        session_id: app.sessionId,
        appid: app.appid,
        activity_id: app.activityId,
        keyword: page.data.keyword,
        rank: ''
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

  //执行搜索
  searchinfo: function (info) {
    if (info.detail.value.keyword == "") {
      wx.showToast({
        title: '搜索内容不能为空'
      })
    } else {
      var page = this;
      page.setData({
        page: 1,
        keyword: info.detail.value.keyword
      });
      wx.request({
        url: app.config.apiUrl + 'apply/get_apply_list.php',
        method: 'POST',
        data: {
          page: page.data.page,
          session_id: app.sessionId,
          appid: app.appid,
          activity_id: app.activityId,
          keyword: info.detail.value.keyword,
          rank: 1
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
            voteList: res.data.data
          });
        }
      }
    }
  },
  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
    return {
      path: '/pages/search/search?activity_id=' + app.activityId + "&keyword=" + this.data.keyword
    }
  },
})
