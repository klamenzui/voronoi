<?php
/**
 * Created by PhpStorm.
 * User: Deik
 * Date: 31.07.2018
 * Time: 0:41
 */
require_once("Point.php");

class PointChunk
{
    public $x = 0;
    public $y = 0;
    private $z = 0;
    private $id = "";
    public $edges = [];
    public function __construct($width, $height, $length, $rand){
        if($rand){
            $angle = ((rand(0,999999)/1000000)*pi()*2);
            $radius = [(((rand(0,999999)/1000000)* ($width*0.4 - 0)) + 0), (((rand(0,999999)/1000000)* ($height * 0.5 - 0)) + 0)];
            $this->x = round($radius[0] * cos($angle)) + $width/2;
            $this->y = round($radius[1] * sin($angle)) + $height/2;
            $this->edges = [];
            if($length != null){
                $radius = [$width-($width/1.7), $height-($height/2), $length-($length/2)];
                $this->z = round(rand(0, $length) + $radius[3] *sin($angle))+ $length/2;
                $this->id = $this->x."-".$this->y."-".$this->z;
            }else{
                $this->z = $length;
                $this->id = $this->x."-".$this->y;
            }
        }else{
            // On stocke les valeurs
            $this->x = $width;
            $this->y = $height;
            $this->z = $length;
            if($length != null){
                $this->id = $this->x."-".$this->y."-".$this->z;
            }else{
                $this->id = $this->x."-".$this->y;
            }
        }
    }
    public function getId(){
        return $this->id;
    }
    public function sort($points){
        uasort($points,'cmp');
        return $points;
    }
    public function midpoint ($a, $b) {
        return new Point(($a->x+$b->x)/2,($a->y+$b->y)/2,null,false);
    }
    public function length($a, $b) {
        return sqrt(abs(($b->x-$a->x)*($b->x-$a->x) + ($b->y - $a->y)*($b->y - $a->y)));
    }
    public function checkPoint($point){
        return (($this->x==$point->x)&&($this->y==$point->y))?true:false;
    }
    public function IsPointInsidePolygon ($bords)
    {
        //i1, i2, n, N, S, S1, S2, S3, flag;
        //echo "---------------------------------------<br>";
        $N = count($bords);
        for ($n=0; $n<$N; $n++)
        {
            $flag = 0;
            $i1 = $n < $N-1 ? $n + 1 : 0;
            while ($flag == 0)
            {
                $i2 = $i1 + 1;
                if ($i2 >= $N)
                    $i2 = 0;
                if ($i2 == ($n < $N-1 ? n + 1 : 0))
                    break;
                $S = abs ($bords[$i1]->x * ($bords[$i2]->y - $bords[$n ]->y) + $bords[$i2]->x * ($bords[$n ]->y - $bords[$i1]->y) + $bords[$n]->x  * ($bords[$i1]->y - $bords[$i2]->y));
                $S1 = abs ($bords[$i1]->x * ($bords[$i2]->y - $this->y) + $bords[$i2]->x * ($this->y - $bords[$i1]->y) + $this->x * ($bords[$i1]->y - $bords[$i2]->y));
                $S2 = abs ($bords[$n ]->x * ($bords[$i2]->y - $this->y) + $bords[$i2]->x * ($this->y - $bords[$n ]->y) + $this->x * ($bords[$n ]->y - $bords[$i2]->y));
                $S3 = abs ($bords[$i1]->x * ($bords[$n ]->y - $this->y) + $bords[$n]->x * ($this->y - $bords[$i1]->y) + $this->x * ($bords[$i1]->y - $bords[$n ]->y));
                //if(strnatcmp($this->id,"300_0")!=0){echo "sum:".$S." = ".($S1 + $S2 + $S3)."/S:".$S." - S1:".$S1." - S2:".$S2." - S3:".$S3."<br>";
                //print_r($this);}
                //if(strnatcmp($this->id,"75_225")!=0){echo "sum:".$S." = ".($S1 + $S2 + $S3)."/S:".$S." + S1:".$S1." + S2:".$S2." + S3:".$S3."<br>";
                //echo $this->x.$this->y;
                if ($S == $S1 + $S2 + $S3)
                {
                    $flag = 1;
                    //echo "x:".$this->x."- y:".$this->y;
                    //echo "flag:".$flag."<br>";
                    return $flag;
                }
                $i1 = $i1 + 1;
                if ($i1 >= $N)
                    $i1 = 0;
            }
            if ($flag == 0)
                break;
        }
        return $flag;
    }
    public function closeEdge(){
        echo "<pre>";
            //print_r($this->edges);
        echo "</pre>";
        $keybord = key($this->edges);
        $tmpborder = $this->edges[$keybord];
        $Ledge = $tmpborder;
        $Redge = $tmpborder;
        $newborders[$keybord] = $tmpborder;
        $lenmin = $tmpborder->lengthMidpoint($this);
        $minId = $tmpborder->getId();
        $A = $tmpborder->getA();
        $a = $tmpborder->getA();
        $B = $tmpborder->getB();
        $b = $tmpborder->getB();
        echo($this->getId()."<br>");
        $arr = [];
        while (!$a->checkpoint($b)){
            $minedge = 99999999;
            foreach ($this->edges as $k => $ed) {
                echo $ed->lengthMidpoint($this)."<br>";
                if (($ed->lengthMidpoint($this) < $minedge)&&($ed->getLength()>0)){
                    $minedge = $ed->lengthMidpoint($this);
                    $tmpborder = $ed;
                    $keybord = $k;
                    $A = $ed->getA();
                    $B = $ed->getB();
                }
                if ($minedge < $lenmin) {
                    $newborders = [];
                    $keybord = $k;
                    $tmpborder = $ed;
                    $Ledge = $tmpborder;
                    $Redge = $tmpborder;
                    $newborders[$keybord] = $tmpborder;
                    $lenmin = $minedge;
                    $minId = $tmpborder->getId();
                    $A = $tmpborder->getA();
                    $a = $tmpborder->getA();
                    $B = $tmpborder->getB();
                    $b = $tmpborder->getB();
                    break;
                }
            }
            echo $minedge.":".$lenmin."<br>";
            if(($minId != $tmpborder->getId())&&($tmpborder->getLength()>0)){
                /*echo "<pre>";
                print_r($tmpborder->getAB());
                //print_r($Ledge->getAB());
                //print_r($Redge->getAB());
                echo "</pre>";//($tmpborder->checkParaler($Ledge))?"1":"0";*/
                if($A->checkPoint($a)&&($tmpborder->checkParaler($Ledge))){
                    echo "true1<br>";
                    $newborders[$keybord] = $tmpborder;
                    unset($this->edges[$keybord]);
                    $a = $tmpborder->getB();
                    $Ledge = $tmpborder;
                    foreach ($arr as $key => $el) {
                        $this->edges[$key] = $el;
                        unset($arr[$key]);
                    }
                }else{
                    //echo "uhijo".$B->getId().":".$Redge->getId()."<br>";
                    //print_r($tmpborder);
                if($B->checkPoint($b)&&($tmpborder->checkParaler($Redge))){
                    echo "true2".$B->getId().$b->getId()."<br>";
                    $newborders[$keybord] = $tmpborder;
                    unset($this->edges[$keybord]);
                    $b = $tmpborder->getA();
                    $Redge = $tmpborder;
                    foreach ($arr as $key => $el) {
                        $this->edges[$key] = $el;
                        unset($arr[$key]);
                    }
                }else
                if($B->checkPoint($a)&&($tmpborder->checkParaler($Ledge))){
                    echo "true3<br>";
                    $newborders[$keybord] = $tmpborder;
                    unset($this->edges[$keybord]);
                    $a = $tmpborder->getA();
                    $Ledge = $tmpborder;
                    foreach ($arr as $key => $el) {
                        $this->edges[$key] = $el;
                        unset($arr[$key]);
                    }
                }else
                if($A->checkPoint($b)&&($tmpborder->checkParaler($Redge))){
                    echo "true4<br>";
                    $newborders[$keybord] = $tmpborder;
                    unset($this->edges[$keybord]);
                    $b = $tmpborder->getB();
                    $Redge = $tmpborder;
                    foreach ($arr as $key => $el) {
                        $this->edges[$key] = $el;
                        unset($arr[$key]);
                    }
                }else{
                    $arr[$keybord] = $tmpborder;
                    unset($this->edges[$keybord]);
                }}
            }else{
                echo "...............................<br>";
                unset($this->edges[$keybord]);
            }
            echo $a->x."-".$a->y.":".$b->x."-".$b->y."<br>";
            echo "-------------------------------------------------------------------<br>";
        }
        echo "<pre>";
        print_r($newborders);
        echo "</pre>";
        //$this->edges = $newborders;
    }
}