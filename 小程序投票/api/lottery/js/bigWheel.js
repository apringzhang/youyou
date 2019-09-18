var loadData = [
  {
    name: "bg",
    path: publicDir + "/images/bg.jpg"
  },
  {
    name: "bigWheel",
    path: publicDir + "/images/bigWheel.png"
  },
  {
    name: "arrow",
    path: publicDir + "/images/arrow.png"
  },
  {
    name: "congratulation",
    path: publicDir + "/images/congratulation.png"
  },
  {
    name: "getit",
    path: publicDir + "/images/getit.png"
  },
  {
    name: "thankyou",
    path: publicDir + "/images/thankyou.png"
  },
  {
    name: "close",
    path: publicDir + "/images/close.png"
  }
];
var loadingLayer;
var dataList = [];
/**
 * 精灵
 */
var bg;
var bigWheel;
var arrow;
var thankyou;
var close;
var congratulation;
var getit;
var giftNameText;
/**
 * 参数
 */
//礼物ID
var giftId = 0;
//礼物名
var giftName = "";
//抽奖结果(0:不限数量的礼物 1:限制数量的礼物)
var giftResult = 0;
//最高速度
var maxSpeed = 30;
//记录当前速度
var speed = 0;
//加速度
var speedv = 0.2;
//记录进入稳定状态时到目前时间的角度
var rotatingStart = 0;
//稳定旋转圈数
var rotatingTotal = 1;
//稳定运行与结束时转盘的偏移角度
var offset = 105;
//缩放等级
var scale = 0;
//应用状态
var status = 1;
function main() {
  loadingLayer = new LoadingSample2();
  addChild(loadingLayer);
  LLoadManage.load(
    loadData,
    function(progress) {
      loadingLayer.setProgress(progress);
    },
    init
  );
}

function init(result) {
  //缩放
  dataList = result;
  //背景
  bg = new LSprite();
  bg.addChild(new LBitmap(new LBitmapData(dataList["bg"])));
  scale = width / bg.getWidth();
  bg.scaleX = scale;
  bg.scaleY = scale;
  addChild(bg);
  //大转盘
  bigWheel = new LSprite();
  var bigWheelBitmap = new LBitmap(new LBitmapData(dataList["bigWheel"]));
  bigWheelBitmap.x = -bigWheelBitmap.width / 2;
  bigWheelBitmap.y = -bigWheelBitmap.height / 2;
  bigWheel.addChild(bigWheelBitmap);
  bigWheel.x = width / 2;
  bigWheel.y = 720 * scale;
  bigWheel.scaleX = scale;
  bigWheel.scaleY = scale;
  addChild(bigWheel);
  //指针
  arrow = new LSprite();
  var arrowBitmap = new LBitmap(new LBitmapData(dataList["arrow"]));
  arrowBitmap.x = -arrowBitmap.width / 2;
  arrowBitmap.y = (-arrowBitmap.height - 50) / 2;
  arrow.addChild(arrowBitmap);
  arrow.addEventListener(LMouseEvent.MOUSE_DOWN, start);
  arrow.x = width / 2;
  arrow.y = 720 * scale;
  arrow.scaleX = scale;
  arrow.scaleY = scale;
  addChild(arrow);
  //谢谢参与
  thankyou = new LSprite();
  thankyou.addChild(new LBitmap(new LBitmapData(dataList["thankyou"])));
  thankyou.scaleX = scale;
  thankyou.scaleY = scale;
  thankyou.y -= scale * 200;
  //addChild(thankyou);
  //关闭按钮
  close = new LSprite();
  var closeBitmap = new LBitmap(new LBitmapData(dataList["close"]));
  closeBitmap.x = -closeBitmap.width / 2;
  close.addChild(closeBitmap);
  close.scaleX = scale;
  close.scaleY = scale;
  close.x = width / 2;
  close.y += scale * 880;
  close.addEventListener(LMouseEvent.MOUSE_DOWN, closeThankyou);
  //addChild(close);
  //恭喜中奖
  congratulation = new LSprite();
  congratulation.addChild(
    new LBitmap(new LBitmapData(dataList["congratulation"]))
  );
  congratulation.scaleX = scale;
  congratulation.scaleY = scale;
  congratulation.y -= scale * 200;
  //addChild(congratulation);
  //立即领取按钮
  getit = new LSprite();
  var getitBitmap = new LBitmap(new LBitmapData(dataList["getit"]));
  getitBitmap.x = -getitBitmap.width / 2;
  getit.addChild(getitBitmap);
  getit.scaleX = scale;
  getit.scaleY = scale;
  getit.x = width / 2;
  getit.y += scale * 880;
  getit.addEventListener(LMouseEvent.MOUSE_DOWN, jumpToMyHistory);
  //addChild(getit);
  //ENTER_FRAME
  LGlobal.stage.addEventListener(LEvent.ENTER_FRAME, enterFrameHandler);
}
function enterFrameHandler(event) {
  bigWheel.rotate += speed;
  switch (status) {
    case "1":
      break;
    case "2":
      startRotate();
      break;
    case "3":
      rotating();
      break;
    case "4":
      endRotate();
      break;
    case "5":
      end();
      break;
  }
}

function startRotate() {
  speed += speedv;
  if (speed >= maxSpeed) {
    speed = maxSpeed;
    status = 3;
    rotatingStart = 0;
  }
}

function rotating() {
  rotatingStart += speed;
  if (rotatingStart >= rotatingTotal * 360) {
    bigWheel.rotate = -offset - rotate;
    status = 4;
  }
}

function endRotate() {
  speed -= speedv;
  if (speed <= 0) {
    speed = 0;
    status = 5;
    rotatingStart = 0;
  }
}

function start(event) {
  //请求接口
  $.post(
    "push_lottery.php",
    {
      session_id: sessionId,
      activity_id: activityId
    },
    function(result) {
      if (result.result == 0 || result.result == 1) {
        giftId = result.data.id;
        giftName = result.data.name;
        rotate = result.data.location;
        giftResult = result.result;
        arrow.removeEventListener(LMouseEvent.MOUSE_DOWN, start);
        status = 2;
      } else {
        layui.use("layer", function() {
          var layer = layui.layer;
          layer.msg(result.message);
        });
      }
    },
    "json"
  );
}

function end() {
  arrow.addEventListener(LMouseEvent.MOUSE_DOWN, start);
  if (giftResult == 0) {
    showThankyou();
  } else {
    showCongratulation();
  }
  status = 1;
}
/**
 * 谢谢参与页面
 */
function showThankyou() {
  addChild(thankyou);
  addChild(close);
}

/**
 * 礼物页面
 */
function showCongratulation() {
  addChild(congratulation);
  addChild(getit);
  giftNameText = new LTextField();
  giftNameText.text = giftName;
  giftNameText.color = "#ff0000";
  giftNameText.size = 80 * scale;
  giftNameText.x = width / 2 - giftNameText.getWidth() / 2;
  giftNameText.y = 490 * scale;
  addChild(giftNameText);
}
/**
 * 刷新页面
 */
function closeThankyou() {
    removeChild(thankyou);
    removeChild(close);
}

/**
 * 跳转到我的中奖纪录
 */
function jumpToMyHistory() {
    removeChild(congratulation);
    removeChild(getit);
    removeChild(giftNameText);
    wx.miniProgram.navigateTo({url: '/pages/record/index'});
}
