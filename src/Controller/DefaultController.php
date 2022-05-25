<?php

namespace Drupal\bokehtest\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DefaultController.
 */
class DefaultController extends ControllerBase
{
    /**
     * GuzzleHttp\ClientInterface definition.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $httpClient;


    //Set the api enpoint to call
    protected static $endpoint = 'https://metsis.metsis-api.met.no/api/post_datasource';

    //SET HTTP CLIENT DEBUG
    protected static $debug = false;

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        //Container injection
        $instance = parent::create($container);
        $instance->httpClient = $container->get('http_client');

        return $instance;
    }

    /**
     * Examplebokeh.
     *
     * @return array
     *   Return array with bokeh injected divs.
     */
    public function examplebokeh()
    {

    /**
     * JSON REQUEST STRING FOR ENDPOINT
     */
        $json_data =  \Drupal\Component\Serialization\Json::decode('{
        "data": {
          "id1": {
            "title": "osisaf sh icearea seasonal",
            "feature_type": "timeSeries",
            "resources": {
              "opendap": [
                "http://hyrax.epinux.com/opendap/osisaf_sh_icearea_seasonal.nc"
              ]
            }
          },
          "id2": {
            "title": "osisaf nh iceextent daily",
            "feature_type": "timeSeries",
            "resources": {
              "opendap": [
                "http://hyrax.epinux.com/opendap//osisaf_nh_iceextent_daily.nc"
              ]
            }
          },
          "id3": {
            "title": "itp01_itp1grd2042",
            "feature_type": "profile",
            "resources": {
              "opendap": [
                "http://hyrax.epinux.com/opendap/itp01_itp1grd2042.nc"
              ]
            }
          },
          "id4": {
            "title": "itp01_itp1grd2042",
            "feature_type": "NA",
            "resources": {
              "opendap": [
                "http://hyrax.epinux.com/opendap/itp01_itp1grd2042.nc"
              ]
            }
          },
          "id5": {
            "title": "ctdiaoos gi2007 2009",
            "feature_type": "timeSeriesProfile",
            "resources": {
              "opendap": [
                "http://hyrax.epinux.com/opendap/ctdiaoos_gi2007_2009.nc"
              ]
            }
          },
          "id6": {
            "title": "High resolution sea ice concentration",
            "feature_type": "NA",
            "resources": {
              "OGC:WMS": [
                "https://thredds.met.no/thredds/wms/cmems/si-tac/cmems_obs-si_arc_phy-siconc_nrt_L4-auto_P1D_aggregated?service=WMS&version=1.3.0&request=GetCapabilities"
              ]
            }
          },
          "id7": {
            "title": "S1A EW GRDM",
            "feature_type": "NA",
            "resources": {
              "OGC:WMS": [
                "http://nbswms.met.no/thredds/wms_ql/NBS/S1A/2021/05/18/EW/S1A_EW_GRDM_1SDH_20210518T070428_20210518T070534_037939_047A42_65CD.nc?SERVICE=WMS&REQUEST=GetCapabilities"
              ]
            }
          }
        },
        "email": "epiesasha@me.com",
        "project": "METSIS",
        "notebook": true,
        "notebooks": {
          "UseCase2": {
            "name": "UseCase",
            "purpose": "cool science",
            "resource": "https://raw.githubusercontent.com/UseCase.ipynb"
          }
        }
      }');


        //DEFAULT MESSAGE TO RETURN TO PAGE IF SOMETHING GOES WRONG
        $data = "<h2> Ooops Something went wrong!!</h2> Contact Administraor or see logs";

        //Doing API request
        try {
            $response = $this->httpClient->post(
                //'POST',
                self::$endpoint,
                [
                'json' => $json_data,
                'Accept' => 'text/html',
                'Content-Type' => 'application/json',
                'debug' => self::$debug, //Set to true to
              ],
            );

            //GET RESPONSE STATUS CODE
            $responseStatus = $response->getStatusCode();
            \Drupal::logger('bokehtest')->debug("Got response status: @string", ['@string' => $responseStatus ]);

            //Get the response body as a string
            $data = (string) $response->getBody();
            //IF exception occurs. send a drupal message
        } catch (Exception $e) {
            \Drupal::messenger()->addError("Could not contact bokeh dashboard api at @uri .", [ '@uri' => self::$endpoint]);
            \Drupal::messenger()->addError($e);
        }

        $build = [];
        $buiild['dashboard'] = [
          '#type' => 'container',
        ];

        //Create render array with inline template for the dashboard
        $build['dashboard']['dashboard-wrapper'] = [
                '#prefix' => '<div id="bokeh-dashboard" class="dashboard">',
                '#type'     => 'inline_template',
                  '#template' => '{{ dashboard | raw }}', //Raw keyword important for correct html/url escaping
                  '#context'  => [
                    'dashboard' => $data
                  ],
                  '#suffix' => '</div>',
                ];

        $build['#attached'] = [
            'library' => [
              'core/jquery.ui',
              //'leaflet/leaflet',
            ],
          ];
        //Return render array
        return $build;
    }
}
