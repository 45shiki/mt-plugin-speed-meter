<?php
class SpeedMeter extends MTPlugin {
    var $app;
    var $registry = array(
        'name' => 'SpeedMeter',
        'id'   => 'SpeedMeter',
        'key'  => 'speedmeter',
        'config_settings' => array(
            'SpeedMeterDebugScope' => array( 'default' => 'log' ),
        ),
        'tags' => array(
            'block' => array( 'speedmeter' => '_hdlr_speedmeter' ),
        ),
    );

    function _hdlr_speedmeter ( $args, $content, &$ctx, &$repeat ) {
        $localvars = array( 'speedmeter_id' );
        if (! isset( $content ) ) {
            $ctx->localize( $localvars );
            $key = md5( uniqid( rand(), 1 ) . $name );
            $ctx->stash( 'speedmeter_id', $key );
            $ctx->stash( $key, microtime( TRUE ) );
        } else {
            $app = $ctx->stash( 'bootstrapper' );
            $scope = strtolower( $app->config( 'SpeedMeterDebugScope' ) );
            if ( (! $scope ) || ( $scope && $scope == 'none' ) ) {
                $repeat = FALSE;
                return $content;
            }
            $name = $args[ 'name' ];
            if (! $name ) {
                $template_id = $ctx->stash('build_template_id');
                require_once('class.mt_template.php');
                $_template = new Template;
                $where = "template_id = $template_id";
                $result = $_template->Find( $where );
                $template = $result[0];
                $name = $template->name;
            }
            $repeat = FALSE;
            $key = $ctx->stash( 'speedmeter_id' );
            $start = $ctx->stash( $key );
            $end = microtime( TRUE );
            $ctx->restore( $localvars );
            $time = $end - $start;
            $message = $app->translate( 'The template for [_1] have been build.', "'{$name}'" );
            $message .= $app->translate( 'Publish time: [_1].', $time );
            if ( $scope == 'log' ) {
                $app->log(
                    $message,
                    array(
                        'blog_id' => $ctx->stash( 'blog_id' ),
                        'level' => 16,
                        'category' => 'speedmetor',
                    )
                );
            } elseif ( $scope == 'screen' ) {
                $prefix = $args[ 'prefix' ] || '';
                $suffix = $args[ 'suffix' ] || '';
                $content .= $prefix . $message . $suffix;
            }
            return $content;
        }
    }
}
