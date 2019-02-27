# swoole_im
### 基于Yii+Swoole+Redis实现的IM方案

#### 主要功能：
1. 支持群聊
2. 支持头像，昵称
3. 文本消息
4. 支持发送图片
5. 表情（待定）
6. 历史消息

#### WebSocket服务端
- 在连接之后还需要做用户的校验. 
- 需要支持获取历史消息的功能,  
- 客户端 和 服务端之间的信息交换格式还需要确定下来. 
- 支持广播和组播, 广播就是给所有聊天室的所有成员发消息. 
- 组播就是给单个聊天室的成员发送消息.  
- 其他的做的时候遇到什么问题再看  
- 可以直接在 yii的console/controllers下面直接写一个新的控制器作为入口直接利用yii的各种组件.  
- 然后还要考虑做成linux服务的形式用systemctl来管理服务

#### 采用面向对象的形式来写，代码读起来会比较清晰，设计如下核心类

`ChartServer类`：管理websocket服务，信息收发管理

`ChartRoom类`：聊天室管理，聊天室的用户组存储在redis中，一个直播间初始化一个聊天室

+ 创建聊天室
+ 加入聊天室
+ 退出聊天室
+ 解散聊天室
+ 聊天室所有成员

`ChartMsg类`：聊天信息处理，包括存储，类型处理

WebSocket客户端
这个就很简单啦！不作描述


二、具体实现

2.1 目录结构
![](https://github.com/melodyne/swoole_im/blob/master/doc/%E5%9B%BE%E7%89%871.png?raw=true)


2.2 Yii 命令模块的chat控制器
![](https://github.com/melodyne/swoole_im/blob/master/doc/%E5%9B%BE%E7%89%872.png?raw=true)
 