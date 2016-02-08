<?php
/**
 * Pimcore
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2009-2016 pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GNU General Public License version 3 (GPLv3)
 */

namespace Pimcore\Controller\Plugin;

use Pimcore\Tool;
use Pimcore\Google\Analytics as AnalyticsHelper;

class GoogleTagManager extends \Zend_Controller_Plugin_Abstract
{

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * @param \Zend_Controller_Request_Abstract $request
     * @return bool|void
     */
    public function routeShutdown(\Zend_Controller_Request_Abstract $request)
    {
        if (!Tool::useFrontendOutputFilters($request)) {
            return $this->disable();
        }
    }

    /**
     * @return bool
     */
    public function disable()
    {
        $this->enabled = false;
        return true;
    }

    /**
     *
     */
    public function dispatchLoopShutdown()
    {
        if (!Tool::isHtmlResponse($this->getResponse())) {
            return;
        }

        $siteKey = \Pimcore\Tool\Frontend::getSiteKey();
        $reportConfig = \Pimcore\Config::getReportConfig();

        if ($this->enabled && isset($reportConfig->tagmanager->sites->$siteKey->containerId)) {
            $containerId = $reportConfig->tagmanager->sites->$siteKey->containerId;

            if($containerId) {
                $code = <<<CODE
<!-- Google Tag Manager -->
<noscript><iframe src="//www.googletagmanager.com/ns.html?id=$containerId"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','$containerId');</script>
<!-- End Google Tag Manager -->
CODE;

                $body = $this->getResponse()->getBody();

                // insert code after the opening <body> tag
                $body = preg_replace("@<body(>|.*?[^?]>)@", "<body$1\n\n" . $code, $body);

                $this->getResponse()->setBody($body);
            }
        }
    }
}
