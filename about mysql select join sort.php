<?php
/**
 * 首先,我得把问题描述一下,
 * 说来话长,
 * 这个问题,主要关系到两张表:
 *  别墅基本信息表bs2_villabasic
 *      存放的是别墅的一些基本信息
 *  和
 *  活动别墅表bs2_activityvilla
 *      存放的是参加活动的别墅,分别用:act_id活动id,和villa_id别墅id来确定,注意:这里,是多对多的关系
 * 要实现的效果:
 *  排序,新加入活动的别墅排在前面
 * 问题所在:
 *  1.别墅参加活动后,在villabasic表中没有对应的标志,显示这个别墅是最后加入活动的,比如,时间.
 *      其实,应该有一个active_time表示参加活动的时间,这个字段只记录最后一次操作的时间.然而设计的时候没有考虑到
 *  2.activityvilla表中一个villa_id对应多个act_id
 *  3.想用两表联查的方式
 *
 * 排序
 *  按照activity里的id排序,用的SQL语句大概是这样的,
 *  SELECT a.id aid,`v`.* FROM `bs2_villabasic` `v` LEFT JOIN `bs2_activityvilla` `a` ON `a`.`villa_id`=`v`.`id` WHERE  v.status = 1  ORDER BY a.id desc;
 *  当时,是这么想的,左连接 left join 肯定是以basic表位基准,然而,并不是,由于,activityvilla表中有多条记录的值符合`a`.`villa_id`=`v`.`id`,所以,会有重复的别墅出现,
 * 显然,这不是我想要的.如下,
 *  运行语句,
 *  SELECT a.id aid,`v`.id FROM `bs2_villabasic` `v` inner JOIN `bs2_activityvilla` `a` ON `a`.`villa_id`=`v`.`id` WHERE  v.status = 1  ORDER BY a.id desc;
 *  结果,
  aid, id
'454', '283'
'453', '95'
'451', '894'
'450', '893'
'449', '315'
'448', '659'
'434', '28'
'433', '313'
'432', '73'
'431', '331'
'430', '325'
'429', '314'
'428', '306'
'427', '311'
'426', '135'
'425', '88'
'424', '90'
'423', '76'
'422', '289'
'421', '287'
'420', '270'
'419', '269'
'418', '55'
'417', '24'
'371', '792'
'369', '792'
'367', '283'
'366', '95'
'362', '249'
'361', '247'
'360', '246'
'359', '241'
'355', '513'
'339', '135'
'336', '46'
'335', '659'
'334', '90'
'333', '24'
'332', '74'
'331', '308'
'330', '311'
'329', '331'
'328', '55'
'327', '64'
'326', '28'
'325', '306'
'324', '88'
'323', '254'
'317', '95'
'316', '283'
'262', '701'
'260', '245'
'259', '9'
'251', '701'
'250', '684'
'249', '183'
'248', '189'
'247', '181'
'246', '179'
'245', '174'
'244', '81'
'239', '227'
'238', '105'
'237', '104'
'236', '318'
'235', '187'
'234', '653'
'233', '276'
'232', '27'
'231', '162'
'230', '238'
'229', '258'
'228', '684'
'227', '179'
'226', '174'
'225', '189'
'224', '183'
'223', '181'
'222', '81'
'198', '227'
'197', '168'
'196', '35'
'195', '652'
'194', '275'
'193', '219'
'192', '318'
'191', '321'
'190', '47'
'189', '43'
'188', '63'
'187', '130'
'186', '27'
'185', '194'
'184', '187'
'183', '238'
'182', '272'
'181', '276'
'180', '258'
'179', '653'
'178', '279'
'177', '280'
'176', '307'
'175', '278'
'174', '162'
'173', '71'
'172', '182'
'171', '104'
'170', '534'
'169', '820'
'168', '191'
'167', '217'
'166', '163'
'165', '108'
'164', '105'
'163', '72'
'162', '68'
'161', '58'
'160', '39'
'159', '19'
'155', '278'
'154', '39'
'153', '117'
'152', '77'
'149', '120'
'148', '126'
'147', '254'
'146', '111'
'145', '65'
'144', '364'
'136', '659'
'135', '250'
'133', '117'
'132', '77'
'131', '315'
'130', '76'
'126', '90'
'125', '314'
'124', '135'
'123', '49'
'120', '65'
'119', '278'
'118', '370'
'117', '39'
'109', '273'
'107', '232'
'106', '231'
'105', '229'
'104', '120'
'103', '126'
'102', '111'
'99', '291'
'98', '308'
'97', '74'
'96', '24'
'95', '46'
'94', '253'
'93', '15'
'92', '14'

 *
 * 很显然,结果有重复的,比如
 *
'371', '792'
'369', '792'
 * 要去重, distinct 和 group by ,然而,distinct要把所有查出来的字段都比对后去重,试了一下达不到效果,就换group by 了
 * 然而,坑并没有结束,换了group by 之后 order 用的 a.id 取到的是考前面的id,比如villa_id 792,有活动 371,和369,group by 用的是
 * 369.也就是说如果这个别墅,之前参加过活动,然后,我又把他放到新的活动中,本来应该是排在前面的,可是,他会按照最以前的活动的顺序排位置,这就
 * 很不爽了,应该是取最大的a.id啊!
 * 经过一番折腾后,机智的我发现了这条最终的语句
 * SELECT max(a.id),a.id aid,`v`.* FROM `bs2_villabasic` `v` LEFT JOIN `bs2_activityvilla` `a` ON `a`.`villa_id`=`v`.`id` WHERE  (  v.price > 0 and v.status = 1 and v.actstr <> '' ) GROUP BY v.id ORDER BY max(a.id) desc;
 *
 * 主要是后面order这里:
 *  ORDER BY max(a.id) desc,这样的话,他用的就是activity表中的符合条件的最大的id排序,达到目的了.
 *
 * 其实,用php的方式也可以解决,
 * 1.把activity表中的别墅都查出来,id des
 * 2.循环数组,同一个别墅,只保留最大的id,不行不行,这样没法实现分页
 */
