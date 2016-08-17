<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;

class Network extends Model
{
    /**
     * Adds a new network, consisting of
     * a network name and ID, nodes, and targets
     * (other nodes that the node is visible to).
     * @param array $config   Contains the network definition
     *                        in a multi-dimentional array
     */
    public static function addNetwork($config)
    {

      // Save the new network
      $network = new Network();
      $network->name = $config['name'];
      $network->save();

      foreach ($config['nodes'] as $node => $targets) {

        // Save each node...
        $new_node = new \oceler\NetworkNode();
        $new_node->network_id = $network->id;
        $new_node->node = $node;
        $new_node->save();

        foreach ($targets as $target) {
          // ... and save each node's targets
          $edge = new \oceler\NetworkEdge();
          $edge->network_id = $network->id;
          $edge->source = $node;
          $edge->target = $target;
          $edge->save();
        }
      }

    }

    public function nodes()
    {
      return $this->hasMany('oceler\NetworkNode');
    }
}
