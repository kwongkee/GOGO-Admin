<?php

if(!pdo_fieldexists('onljob_config', 'bjtc')) {
    pdo_query("ALTER TABLE ".tablename('onljob_config')." ADD COLUMN `bjtc` decimal(3,2) NOT NULL default '0.00' COMMENT '收费班级提成';");
}
if(!pdo_fieldexists('onljob_theclass', 'kxtimes')) {
    pdo_query("ALTER TABLE ".tablename('onljob_theclass')." ADD COLUMN `kxtimes` int(10) NOT NULL;");
}
if(!pdo_fieldexists('onljob_theclass', 'number')) {
    pdo_query("ALTER TABLE ".tablename('onljob_theclass')." ADD COLUMN `number` int(8) NOT NULL;");
}
if(!pdo_fieldexists('onljob_theclass', 'price')) {
    pdo_query("ALTER TABLE ".tablename('onljob_theclass')." ADD COLUMN `price` decimal(10,2) NOT NULL;");
}
if(!pdo_fieldexists('onljob_work_answer', 'teacher_comment')) {
    pdo_query("ALTER TABLE ".tablename('onljob_work_answer')." ADD COLUMN `teacher_comment` text NOT NULL;");
}
if(!pdo_fieldexists('onljob_work_answer', 'student_comment')) {
    pdo_query("ALTER TABLE ".tablename('onljob_work_answer')." ADD COLUMN `student_comment` text NOT NULL;");
}

//20181127 添加索引 start
if (!pdo_indexexists('onljob_class', 'idx_weid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_class') . " ADD INDEX `idx_weid` (`weid`);");
}
if (!pdo_indexexists('onljob_class', 'idx_type')) {
    pdo_query("ALTER TABLE " . tablename('onljob_class') . " ADD INDEX `idx_type` (`type`);");
}
if (!pdo_indexexists('onljob_class', 'idx_pid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_class') . " ADD INDEX `idx_pid` (`pid`);");
}


if (!pdo_indexexists('onljob_work_fen', 'idx_weid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work_fen') . " ADD INDEX `idx_weid` (`weid`);");
}
if (!pdo_indexexists('onljob_work_fen', 'idx_uid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work_fen') . " ADD INDEX `idx_uid` (`uid`);");
}
if (!pdo_indexexists('onljob_work_fen', 'idx_wid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work_fen') . " ADD INDEX `idx_wid` (`wid`);");
}
if (!pdo_indexexists('onljob_work_fen', 'idx_bjid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work_fen') . " ADD INDEX `idx_bjid` (`bjid`);");
}
if (!pdo_indexexists('onljob_work_fen', 'idx_state')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work_fen') . " ADD INDEX `idx_state` (`state`);");
}
if (!pdo_indexexists('onljob_work_fen', 'idx_stratimes')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work_fen') . " ADD INDEX `idx_stratimes` (`stratimes`);");
}
if (!pdo_indexexists('onljob_work_fen', 'idx_dateline')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work_fen') . " ADD INDEX `idx_dateline` (`dateline`);");
}


if (!pdo_indexexists('onljob_work', 'idx_weid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work') . " ADD INDEX `idx_weid` (`weid`);");
}
if (!pdo_indexexists('onljob_work', 'idx_uid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work') . " ADD INDEX `idx_uid` (`uid`);");
}
if (!pdo_indexexists('onljob_work', 'idx_bjid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work') . " ADD INDEX `idx_bjid` (`bjid`);");
}
if (!pdo_indexexists('onljob_work', 'idx_releaset')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work') . " ADD INDEX `idx_releaset` (`releaset`);");
}
if (!pdo_indexexists('onljob_work', 'idx_state')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work') . " ADD INDEX `idx_state` (`state`);");
}


if (!pdo_indexexists('onljob_theclass', 'idx_weid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_theclass') . " ADD INDEX `idx_weid` (`weid`);");
}
if (!pdo_indexexists('onljob_theclass', 'idx_uid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_theclass') . " ADD INDEX `idx_uid` (`uid`);");
}


if (!pdo_indexexists('onljob_work_questions', 'idx_qid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work_questions') . " ADD INDEX `idx_qid` (`qid`);");
}
if (!pdo_indexexists('onljob_work_questions', 'idx_weid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work_questions') . " ADD INDEX `idx_weid` (`weid`);");
}
if (!pdo_indexexists('onljob_work_questions', 'idx_uid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work_questions') . " ADD INDEX `idx_uid` (`uid`);");
}
if (!pdo_indexexists('onljob_work_questions', 'idx_wid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work_questions') . " ADD INDEX `idx_wid` (`wid`);");
}
if (!pdo_indexexists('onljob_work_questions', 'idx_bjid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work_questions') . " ADD INDEX `idx_bjid` (`bjid`);");
}


if (!pdo_indexexists('onljob_work_answer', 'idx_fid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work_answer') . " ADD INDEX `idx_fid` (`fid`);");
}
if (!pdo_indexexists('onljob_work_answer', 'idx_weid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work_answer') . " ADD INDEX `idx_weid` (`weid`);");
}
if (!pdo_indexexists('onljob_work_answer', 'idx_uid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work_answer') . " ADD INDEX `idx_uid` (`uid`);");
}
if (!pdo_indexexists('onljob_work_answer', 'idx_qid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work_answer') . " ADD INDEX `idx_qid` (`qid`);");
}
if (!pdo_indexexists('onljob_work_answer', 'idx_wid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work_answer') . " ADD INDEX `idx_wid` (`wid`);");
}
if (!pdo_indexexists('onljob_work_answer', 'idx_bjid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work_answer') . " ADD INDEX `idx_bjid` (`bjid`);");
}


if (!pdo_indexexists('onljob_theclass_apply', 'idx_weid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_theclass_apply') . " ADD INDEX `idx_weid` (`weid`);");
}
if (!pdo_indexexists('onljob_theclass_apply', 'idx_uid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_theclass_apply') . " ADD INDEX `idx_uid` (`uid`);");
}
if (!pdo_indexexists('onljob_theclass_apply', 'idx_bjid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_theclass_apply') . " ADD INDEX `idx_bjid` (`bjid`);");
}
if (!pdo_indexexists('onljob_theclass_apply', 'idx_tuid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_theclass_apply') . " ADD INDEX `idx_tuid` (`tuid`);");
}



if (!pdo_indexexists('onljob_questions', 'idx_weid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_questions') . " ADD INDEX `idx_weid` (`weid`);");
}
if (!pdo_indexexists('onljob_questions', 'idx_uid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_questions') . " ADD INDEX `idx_uid` (`uid`);");
}
if (!pdo_indexexists('onljob_questions', 'idx_parentid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_questions') . " ADD INDEX `idx_parentid` (`parentid`);");
}
if (!pdo_indexexists('onljob_questions', 'idx_status')) {
    pdo_query("ALTER TABLE " . tablename('onljob_questions') . " ADD INDEX `idx_status` (`status`);");
}

//////////////////////////////////////

if (!pdo_indexexists('onljob_practice_fen', 'idx_weid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_practice_fen') . " ADD INDEX `idx_weid` (`weid`);");
}
if (!pdo_indexexists('onljob_practice_fen', 'idx_uid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_practice_fen') . " ADD INDEX `idx_uid` (`uid`);");
}
if (!pdo_indexexists('onljob_practice_fen', 'idx_wid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_practice_fen') . " ADD INDEX `idx_wid` (`wid`);");
}
if (!pdo_indexexists('onljob_practice_fen', 'idx_bjid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_practice_fen') . " ADD INDEX `idx_bjid` (`bjid`);");
}
if (!pdo_indexexists('onljob_practice_fen', 'idx_state')) {
    pdo_query("ALTER TABLE " . tablename('onljob_practice_fen') . " ADD INDEX `idx_state` (`state`);");
}
if (!pdo_indexexists('onljob_practice_fen', 'idx_stratimes')) {
    pdo_query("ALTER TABLE " . tablename('onljob_practice_fen') . " ADD INDEX `idx_stratimes` (`stratimes`);");
}
if (!pdo_indexexists('onljob_practice_fen', 'idx_dateline')) {
    pdo_query("ALTER TABLE " . tablename('onljob_practice_fen') . " ADD INDEX `idx_dateline` (`dateline`);");
}



if (!pdo_indexexists('onljob_practice_answer', 'idx_fid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_practice_answer') . " ADD INDEX `idx_fid` (`fid`);");
}
if (!pdo_indexexists('onljob_practice_answer', 'idx_weid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_practice_answer') . " ADD INDEX `idx_weid` (`weid`);");
}
if (!pdo_indexexists('onljob_practice_answer', 'idx_uid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_practice_answer') . " ADD INDEX `idx_uid` (`uid`);");
}
if (!pdo_indexexists('onljob_practice_answer', 'idx_qid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_practice_answer') . " ADD INDEX `idx_qid` (`qid`);");
}
if (!pdo_indexexists('onljob_practice_answer', 'idx_wid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_practice_answer') . " ADD INDEX `idx_wid` (`wid`);");
}
if (!pdo_indexexists('onljob_practice_answer', 'idx_bjid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_practice_answer') . " ADD INDEX `idx_bjid` (`bjid`);");
}



if (!pdo_indexexists('onljob_practice_answer_pz', 'idx_fid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_practice_answer_pz') . " ADD INDEX `idx_fid` (`fid`);");
}
if (!pdo_indexexists('onljob_practice_answer_pz', 'idx_weid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_practice_answer_pz') . " ADD INDEX `idx_weid` (`weid`);");
}
if (!pdo_indexexists('onljob_practice_answer_pz', 'idx_uid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_practice_answer_pz') . " ADD INDEX `idx_uid` (`uid`);");
}
if (!pdo_indexexists('onljob_practice_answer_pz', 'idx_wid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_practice_answer_pz') . " ADD INDEX `idx_wid` (`wid`);");
}
if (!pdo_indexexists('onljob_practice_answer_pz', 'idx_bjid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_practice_answer_pz') . " ADD INDEX `idx_bjid` (`bjid`);");
}
if (!pdo_indexexists('onljob_practice_answer_pz', 'idx_qid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_practice_answer_pz') . " ADD INDEX `idx_qid` (`qid`);");
}


if (!pdo_indexexists('onljob_work_answer_pz', 'idx_fid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work_answer_pz') . " ADD INDEX `idx_fid` (`fid`);");
}
if (!pdo_indexexists('onljob_work_answer_pz', 'idx_weid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work_answer_pz') . " ADD INDEX `idx_weid` (`weid`);");
}
if (!pdo_indexexists('onljob_work_answer_pz', 'idx_uid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work_answer_pz') . " ADD INDEX `idx_uid` (`uid`);");
}
if (!pdo_indexexists('onljob_work_answer_pz', 'idx_wid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work_answer_pz') . " ADD INDEX `idx_wid` (`wid`);");
}
if (!pdo_indexexists('onljob_work_answer_pz', 'idx_bjid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work_answer_pz') . " ADD INDEX `idx_bjid` (`bjid`);");
}
if (!pdo_indexexists('onljob_work_answer_pz', 'idx_qid')) {
    pdo_query("ALTER TABLE " . tablename('onljob_work_answer_pz') . " ADD INDEX `idx_qid` (`qid`);");
}
//20181127 添加索引 end

//20190222 添加模板消息star
if(!pdo_fieldexists('onljob_config', 'mb_open')) {
    pdo_query("ALTER TABLE ".tablename('onljob_config')." ADD COLUMN `mb_open` tinyint(1) NOT NULL default '0' COMMENT '模版消息开关';");
}
if(!pdo_fieldexists('onljob_config', 'mbid1')) {
    pdo_query("ALTER TABLE ".tablename('onljob_config')." ADD COLUMN `mbid1` varchar(250) NOT NULL COMMENT '模版消息1';");
}
if(!pdo_fieldexists('onljob_config', 'mbid2')) {
    pdo_query("ALTER TABLE ".tablename('onljob_config')." ADD COLUMN `mbid2` varchar(250) NOT NULL COMMENT '模版消息2';");
}
if(!pdo_fieldexists('onljob_config', 'mbid3')) {
    pdo_query("ALTER TABLE ".tablename('onljob_config')." ADD COLUMN `mbid3` varchar(250) NOT NULL COMMENT '模版消息3';");
}
if(!pdo_fieldexists('onljob_config', 'mbid4')) {
    pdo_query("ALTER TABLE ".tablename('onljob_config')." ADD COLUMN `mbid4` varchar(250) NOT NULL COMMENT '模版消息4';");
}
if(!pdo_fieldexists('onljob_config', 'mbid5')) {
    pdo_query("ALTER TABLE ".tablename('onljob_config')." ADD COLUMN `mbid5` varchar(250) NOT NULL COMMENT '模版消息5';");
}
if(!pdo_fieldexists('onljob_config', 'mbid6')) {
    pdo_query("ALTER TABLE ".tablename('onljob_config')." ADD COLUMN `mbid6` varchar(250) NOT NULL COMMENT '模版消息6';");
}
//20190222 添加模板消息end

?>