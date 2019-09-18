// pages/chat/chat.js
const app = getApp();
var common = require('../../js/common.js');
var showNetworkError = common.showNetworkError;

Page({

  /**
   * 页面的初始数据
   */
  data: {
    showView: true,
    show: true,
    shownick: true,
    talk:false,
    hiddensave: true,
    images: '../../images/tjtp.png',
  },
  bindhide:function () {
    this.setData({
      showView: true,
      show: true,
      shownick: true
    })
  },
  onChange(event) {
    var pages = this;
    // 需要手动对 checked 状态进行更新
    if (event.detail == true) {
      var detail = 1;
    } else {
      var detail = 0;
    }
    wx.request({
      url: getApp().config.apiUrl + 'group/groups_isaudit',
      method: 'POST',
      data: {
        g_id: pages.data.gid,
        isaudit: detail
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
          checked: event.detail
        })
      }
    }
  },
  onDisturb(event) {
    var pages = this;
    if (event.detail == true) {
      var detail = 1;
    } else {
      var detail = 0;
    }
    wx.request({
      url: getApp().config.apiUrl + 'group/trouble_group',
      method: 'POST',
      data: {
        g_id: pages.data.gid,
        session_id: app.sessionId,
        is_promottone: detail
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
          disturb: event.detail
        })
      }
    }
  },
  onTalk(event) {
    var pages = this;
    if (event.detail == true) {
      var detail = 1;
    } else {
      var detail = 0;
    }
    wx.request({
      url: getApp().config.apiUrl + 'group/sticky_group',
      method: 'POST',
      data: {
        g_id: pages.data.gid,
        session_id: app.sessionId,
        is_top: detail
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
          talk: event.detail
        })
      }
    }
  },
  onShowinfor(event) {
    var pages = this;
    if (event.detail == true) {
      var detail = 1;
    } else {
      var detail = 0;
    }
    wx.request({
      url: getApp().config.apiUrl + 'group/remarks_group',
      method: 'POST',
      data: {
        g_id: pages.data.gid,
        session_id: app.sessionId,
        is_dis_remarks: detail
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
          showinfor: event.detail
        })
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
  turnMenlist:function(){
    var pages = this;
    pages.setData({
      hiddensave: false,
      images: getApp().config.apiUrl + 'group/code?appid=' + app.appid + '&gid=' + pages.data.gid + '&session_id=' + app.sessionId

    })
    console.log(pages.data.images);
  },

  openConfirm: function () {
    var page = this;
    wx.showModal({
        title: '',
        content: '删除聊天记录',
        confirmText: "确定",
        cancelText: "取消",
        success: function (res) {
            console.log(res);
            if (res.confirm) {
              app.grouplist[page.data.gid] = [];
              wx.setStorageSync("grouplist", app.grouplist);
              wx.showToast({
                title: '清除完毕',
              })
              console.log('用户点击主操作')
            }else{
                console.log('用户点击辅助操作')
            }
        }
    });
  },

  delgroups: function (pullDown = false) {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'group/del_group',
      method: 'POST',
      data: {
        g_id: pages.data.gid,
        session_id: app.sessionId
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
        wx.switchTab({
          url: '/pages/surpergroup/surpergroup',
          });
      }
    }
  },

  delConfirm: function () {
    var page = this;
    wx.showModal({
      title: '',
      content: '删除并退出该群',
      confirmText: "确定",
      cancelText: "取消",
      success: function (res) {
        if (res.confirm) {
          page.delgroups();
          console.log('用户点击主操作')
        } else {
          console.log('用户点击辅助操作')
        }
      }
    });
  },

  opensupname: function () {
    var that = this;
    that.setData({
      showView: (!that.data.showView)
    })
  },
  opensupnotice: function () {
    var that = this;
    that.setData({
      show: (!that.data.show)
    })
  },
  
  opennickname: function () {
    var that = this;
    that.setData({
      shownick: (!that.data.shownick)
    })
  },

  // opensupname: function () {
  //   var that = this;
  //   that.setData({
  //     showView: (!that.data.showView)
  //   })
  // },
  
  /**
   * 生命周期函数--监听页面加载
   */
  save: function () {
    this.saveShareImage();
  },

  cancel: function () {

    this.setData({
      hiddensave: true,

    })

  },

  onLoad: function (options) {
    this.setData({
      gid: options.gid,
    });

  },

  //获取群成员列表
  getGouplist: function (pullDown = false) {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'group/groups_touser',
      method: 'POST',
      data: {
        g_id: pages.data.gid
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
          items: res.data.data,
        })
        if (pullDown) {
          wx.stopPullDownRefresh();
        }
      }
    }
  },

  //获取群信息
  getGoupcli: function (pullDown = false) {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'group/cli_group',
      method: 'POST',
      data: {
        g_id: pages.data.gid,
        session_id: app.sessionId
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
          groupInfo: res.data.data,
          qname: res.data.data.name,
          qnotice: res.data.data.notice
        });
        if (res.data.data.isaudit == 1) {
          pages.setData({
            checked: true,
          });
        } else {
          pages.setData({
            checked: false,
          });
        }
        if (res.data.data.is_promottone == 1) {
          pages.setData({
            disturb: true,
          });
        } else {
          pages.setData({
            disturb: false,
          });
        }
        if (res.data.data.is_dis_remarks == 1) {
          pages.setData({
            showinfor: true,
          });
        } else {
          pages.setData({
            showinfor: false,
          });
        }
        if (res.data.data.is_top == 1) {
          pages.setData({
            talk: true,
          });
        } else {
          pages.setData({
            talk: false,
          });
        }
        if (pullDown) {
          wx.stopPullDownRefresh();
        }
      }
    }
  },
  
  clicktar: function() {
    wx.navigateTo({
      url: '/pages/delgroup/delgroup?gid=' + this.data.gid,
    })
  },

  //获取是否是管理员
  getGoupmanage: function (pullDown = false) {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'group/manage_group',
      method: 'POST',
      data: {
        g_id: pages.data.gid,
        session_id: app.sessionId
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
          adminId: res.data.data,
        })
        if (pullDown) {
          wx.stopPullDownRefresh();
        }
      }
    }
  },

  //点击保存分享图片按钮
  saveShareImage: function () {
    var page = this;
    wx.downloadFile({
      url: getApp().config.apiUrl + 'group/code?appid=' + app.appid + '&gid=' + page.data.gid + '&session_id=' + app.sessionId,
      success: function (res) {
        console.log(res);
        if (res.statusCode === 200) {
          wx.saveImageToPhotosAlbum({
            filePath: res.tempFilePath
          });
        }
      }
    })
  },

  //获取群成员列表
  bindradios: function () {
    if (this.data.adminId != 1) {
      wx.showToast({
        title: '您不是管理员无法使用该功能',
        icon: 'none'
      })
      return;
    }
    wx.navigateTo({
      url: '/pages/radios/radios?gid=' + this.data.gid,
    })
  },

  bindname: function (e) {
    this.setData({
      qname: e.detail.value
    })
  },

  bindnotice: function (e) {
    this.setData({
      qnotice: e.detail.value
    })
  },

  bindnick: function (e) {
    this.setData({
      qnick: e.detail.value
    })
  },

  //修改群名称
  subname: function (pullDown = false) {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'group/groups_name',
      method: 'POST',
      data: {
        g_id: pages.data.gid,
        group_name: pages.data.qname
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
        pages.getGoupcli();
        pages.setData({
          showView: true,
          show: true,
          shownick: true
        })
        if (pullDown) {
          wx.stopPullDownRefresh();
        }
      }
    }
  },

  //修改群公告
  subnotice: function (pullDown = false) {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'group/groups_notice',
      method: 'POST',
      data: {
        g_id: pages.data.gid,
        notice: pages.data.qnotice
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
        pages.getGoupcli();
        pages.setData({
          showView: true,
          show: true,
          shownick: true
        })
        if (pullDown) {
          wx.stopPullDownRefresh();
        }
      }
    }
  },

  //修改群名称
  subnick: function (pullDown = false) {
    var pages = this;
    wx.request({
      url: getApp().config.apiUrl + 'group/mybei_group',
      method: 'POST',
      data: {
        g_id: pages.data.gid,
        session_id: app.sessionId,
        username: pages.data.qnick
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
        pages.getGoupcli();
        pages.setData({
          showView: true,
          show: true,
          shownick: true
        })
        if (pullDown) {
          wx.stopPullDownRefresh();
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
  onShow: function () {
    this.getGoupmanage();
    this.getGouplist();
    this.getGoupcli();
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