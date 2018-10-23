<?php
/**
 * Created by PhpStorm.
 * User: Deik
 * Date: 31.07.2018
 * Time: 0:21
 */
    require_once 'PointChunk.php';
    require_once 'Edge.php';

    class Voronoi
    {
        private $metrics = 1; //1-Еклидова, 2-Манхеттенская, 3-Минковского
        private $points = [];
        private $bbox = null;

        public function __construct($metrics, $size) {
            $this->metrics = $metrics;
            $this->bbox = [
                'borders' => $size['borders'],
                'minwidth' => $size['minwidth'],
                'minheight' => $size['minheight'],
                'width' => $size['width'],
                'height' => $size['height']-2
            ];
            foreach ($size['sites'] as $value){
                $this->points[$value->getId()] = $value;
            }

            for ($j=0; $j<count($this->bbox['borders']); $j++) {
                $newbor = new Edge($this->bbox['borders'][$j], (($j + 1) != count($this->bbox['borders'])) ? $this->bbox['borders'][$j + 1] : $this->bbox['borders'][0]);
                foreach ($this->points as $key => $value)
                    $this->points[$key]->edges[$newbor->getId()] = $newbor;
            }
        }
        private function updateEdges($point,$value,$line,$midpoint){
            $A = new Point(999999,999999,null,false);$keyLA = 0;
            $B = new Point(999999,999999,null,false);$keyLB = 0;
            foreach ($point->edges as $k => $val) {
                $tmp = Edge::overlaps($val, $line);
                //if ($tmp != null)echo "tmp!=null--".$tmp->getId()."<br>";
                if ($tmp != null && $tmp->IsPointInsidePolygon($this->bbox['borders'])) {
                    //if(($point->getId()."|".$value->getId())=="150-0|450-0") {echo $val->getId()." :-: ".(Point::length($A, $midpoint).":".Point::length($tmp, $midpoint)."<br>");}
                    if (((PointChunk::length($A, $midpoint) > PointChunk::length($tmp, $midpoint)) || ($A == 999999)) && ($B->getId() != $tmp->getId())) {
                        unset($B);
                        $B = $A;
                        $A = $tmp;
                        unset($keyLB);
                        $keyLB = $keyLA;
                        $keyLA = $k;
                    }
                    if (((PointChunk::length($B, $midpoint) > PointChunk::length($tmp, $midpoint)) || ($B == 999999)) && ($A->getId() != $tmp->getId())) {
                        unset($B);
                        $B = $tmp;
                        unset($keyLB);
                        $keyLB = $k;
                    }
                }
            }
            //echo $midpoint->getId()."<br>A:".$A->x.":".$A->y."<br>";
            //echo "B:".$B->x.":".$B->y."<br>";
            //echo "LA:".$keyLA." : ".$keyLB."<br>Edge:<br>";
            //$point->edges[] = new Edge()
            //unset($point->edges[$keyLA]);
            $idEdges=Edge::newEdge($point, $value, [$A,$B], [$keyLA,$keyLB]);
            /*$VA=Edge::newEdge(, $A, $keyLA);
            $PB=Edge::newEdge($point, $B, $keyLB);
            $VB=Edge::newEdge($value, $B, $keyLB);*/
            $value->edges[$A->getId()."|".$B->getId()]=$point->edges[$A->getId()."|".$B->getId()] = new Edge($A, $B);
            //foreach ($idEdges as $key=>$item){
                //echo $key.":{".$item."}<br>";
            //}
            //echo "AB:{".$A->getId()."|".$B->getId()."}<br>";
           // echo "---------------------------------------------------------<br><br>";
            //echo $point->getId()."|".$value->getId();
            /*if(($point->getId()."|".$value->getId())=="150-0|450-0") {
                echo $point->edges[$A->getId()."|".$B->getId()]->getId()."pro<br>".$midpoint->getId()."<br>";
                print_r($line);
            }
            /*if(strnatcmp($point->getId(),"150-0")!=0) {
                echo "<pre>";
                    print_r($point->edges);
                echo "</pre>";
            }*/
            ksort($point->edges);
            ksort($value->edges);
        }

        public function edges($point){
            //print_r($point->edges);
            //echo "Point P:".$point->getId()."<p>";
            foreach($this->points as $value){
                if(strnatcmp($value->getId(),$point->getId())!=0){
                    //echo "Point V:".$value->getId()."<br>";
                    $line = new Edge($point, $value);
                    $midpoint = PointChunk::midpoint($point,$value);
                    $line->perpendicular($midpoint);
                    /*echo "<pre>";
                        print_r($point);
                    echo "</pre>";*/
                    $this->updateEdges($point,$value,$line,$midpoint);
                    /*echo $point->getId()."|".$value->getId();
                    if(($point->getId()."|".$value->getId())=="150-0|450-0") {
                        echo "<pre>";
                        print_r($line);
                        echo "</pre>";
                        echo($value->getId() . ":" . $point->getId() . ":" . strnatcmp($value->getId(), $point->getId()));
                    }*/
                    //$this->updateEdges($value,$line,$midpoint);
                }
            }
            echo "</p><pre>";
            //print_r($point->edges);
            echo "</pre>";
            $point->closeEdge();
        }

        public function Diagram($bbox) { //Диаграмма
            /*$width=$bbox->width;
            $height=$bbox->height;
            $dist1=$dist0=$j=0;
            $width1=$width-2;
            $height1=$height-2;
            context.fillStyle="white";
            context.fillRect(0,0,width,height);*/
            /*echo "<pre>";
            print_r($this->points);
            echo "</pre><br>--------------------------------------------------------";*/
            ksort($this->points);
            /*echo "<pre>";
            print_r($this->points);
            echo "</pre>";*/
            foreach ($this->points as $point) {
                $this->edges($point);
            }
            foreach ($this->points as $point) {
                echo "<p>id: ".$point->getId()."<br>x:".$point->x."<br>y:".$point->y."<br>edges: <br>";
                foreach ($point->edges as $edge){
                    $ab = $edge->getAB();
                    echo "-  {A: x:".$ab[0]->x." y:".$ab[0]->y.", B: x:".$ab[1]->x." y:".$ab[1]->y."}<br>";
                }
                echo "</p>";
            }
            //print_r($this->points);
            /*for ($y=0; $y<$height; $y++) {
                for ($x=0; $x<$width; $x++) {
                    //console.log(checkArea([x,y],bbox));
                    $tmp = new Point($x,$y,null,false);
                    if($tmp->IsPointInsidePolygon($bbox->borders)){
                        $dist0=$this->Metric($height);
                        $j = -1;
                        for ($i=0; $i<count($sites); $i++) {
                            $dist1 = $this->Metric(new Nurbs_Point($sites[$i][0]-$x,$sites[$i][1]-$y,null,false));
                            if ($dist1 < $dist0) { $dist0=$dist1; $j=$i; }
                        }
                        //context.fillStyle=C[j];
                        //context.fillRect(x,y,1,1);
                    }
                }
            }
            //context.fillStyle="black";
            /*for (var i=0; i<numPoints; i++) {
                    context.fillRect (X[i], Y[i], 3, 3);
                }*/
        }
    }