<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Network extends Model
{
    use HasFactory;


    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function group(): \Illuminate\Database\Eloquent\Relations$1 {
      return $this->hasMany('\App\Group', 'network_id');
    }

    /**
     * Adds a new network, consisting of
     * a network name and ID, nodes, and targets
     * (other nodes that the node is visible to).
     * @param array $config   Contains the network definition
     *                        in a multi-dimentional array
     */
    public static function addNetworkFromConfig($config)
    {

      // Save the new network
      $network = new Network();
      $network->name = $config['name'];
      $network->save();

      foreach ($config['nodes'] as $node => $targets) {

        // Save each node...
        $new_node = new \App\NetworkNode();
        $new_node->network_id = $network->id;
        $new_node->node = $node;
        $new_node->save();

        foreach ($targets as $target) {
          // ... and save each node's targets
          $edge = new \App\NetworkEdge();
          $edge->network_id = $network->id;
          $edge->source = $node;
          $edge->target = $target;
          $edge->save();
        }
      }

    }

    public function nodes(): \Illuminate\Database\Eloquent\Relations$1 {
      return $this->hasMany('App\Models\NetworkNode');
    }

    public static function getAdjacencyMatrix($network_id)
    {
      $nodes = \App\NetworkNode::where('network_id', $network_id)
                                  ->orderBy('node', 'ASC')
                                  ->get();

      $network = "\t";

      foreach ($nodes as $key => $node) { // Write the column headers
        $network .= $node->node ."\t";
      }

      $network .= "\n";

      foreach ($nodes as $key => $node) {

        $network .= $node->node ."\t"; // Write the row
        $edges = \App\NetworkEdge::where('network_id', $network_id)
                                      ->where('source', $node->node)
                                      ->get();

          foreach ($nodes as $n) {
            if($edges->contains('target', $n->node))
              $network .= "1\t";
            else
              $network .= "0\t";
          }

          $network .= "\n";

      }

      return $network;
    }
}
