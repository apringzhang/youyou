var Base64 = require('../../js/base64.min.js').Base64;
Page({
  /**
   * 页面的初始数据
   */
  data: {
  mode:"scaleToFill",
    url:"https://groupmp.honorsoftware.cn/index.php/home/index",
  arr:[
    {url:"../../images/banner.jpg"},
    {url:"../../images/banner1.jpg"},
    {url:"../../images/banner2.jpg"}
  ],
  indicatorDots: true,
  autoplay: true,
  interval: 2000,
  duration: 1000,
  game:[{
    url:'../../images/choujiang.jpg',
    text:"节日大转盘"
  },{
    url:'../../images/egg.jpg',
    text:"砸金蛋"
  },{
    url:'../../images/moreactive.jpg',
    text:"更多精彩活动"
  }
  ],
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    // if (options.scene) {
    //   var page = this;
    //   var scene = Base64.decode(decodeURIComponent(options.scene));
    //   console.log(scene);
    //   //二维码分享入口
    //   var gid = scene.split('_')[0];
    //   app.companyId = sceneCompany.replace('"', "");
    //   var uid = parseInt(scene.split('_')[1]);
    //   if (uid) {
    //     setTimeout(function () {
    //       page.onCouponc(uid)
    //     }, 1500);
    //   }


    // } else {
    //   //正常及转发入口
    //   if (options.gid) {
    //     app.gid = options.gid;
    //   }
    // }
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