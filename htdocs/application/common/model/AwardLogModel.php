<?php

namespace app\common\model;


use think\Model;

/**
 * Class AwardLogModel
 * @package app\common\model
 */
class AwardLogModel extends Model
{
    /**
     * 记录奖励
     * @param $uid
     * @param $award
     * @param $type
     * @param $remark
     * @param array $order
     * @param string $field
     * @return int
     */
    public static function record($uid,$award,$type,$remark,$order=[],$field='credit'){
        $award=$award*100;
        $data=[
            'member_id'=>$uid,
            'order_id'=>0,
            'from_member_id'=>0,
            'type'=>$type,
            'amount'=>$award,
            'real_amount'=>$award,
            'remark'=>$remark,
            'create_time'=>time()
        ];

        if(!empty($order)) {
            if (is_array($order) || is_object($order)) {
                $data['order_id'] = $order['order_id'];
                $data['from_member_id'] = $order['member_id'];
            } else {
                $data['from_member_id'] = intval($order);
            }
        }

        if($type=='commission') {
            money_log([$data['member_id'], $data['from_member_id']], $award, $remark, $type, $field);

        }else{
            money_log([$data['member_id'], $data['from_member_id']], $award, $remark, $type, $field);
        }

        $model=self::create($data);
        return $model['id'];

    }

    public static function rand_award($total,$count,$total_count)
    {
        $min=1;
        $keep=($total_count-$count-1)*$min;

        $max=$total-$keep;
        $remain_count=$total_count-$count;
        if($remain_count==1){
            return round($max,2);
        }
        $avg=$total/$remain_count;

        $max2=min($avg+$avg-$min,$max);


        $rand=1-pow(mt_rand(1,100000),1/5)/10;

        $rand3=mt_rand(1,1000000);
        if($rand3<10){
            $amount = $max2 + $rand * ($max - $max2);
        }else {
            $rand2 = mt_rand(1, 10);
            if ($rand2 > 5) {
                $amount = $avg + $rand * ($max2 - $avg);
            } else {
                $amount = $avg - $rand * ($avg - $min);
            }
        }
        return round($amount);

    }
}