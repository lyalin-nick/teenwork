<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Title
    |--------------------------------------------------------------------------
    |
    | Displayed in title and header.
    |
    */

    'title'              => 'TeenWork',

    /*
    |--------------------------------------------------------------------------
    | Admin Mini logo
    |--------------------------------------------------------------------------
    */
    'logo_mini'          => 'TW',

    /*
    |--------------------------------------------------------------------------
    | Admin Text on sidebar top menu
    |--------------------------------------------------------------------------
    */
    'menu_top'           => 'TeenWork',

    /*
    |--------------------------------------------------------------------------
    | Sidebar default condition class "sidebar-collapse", "sidebar-open"
    |--------------------------------------------------------------------------
    | Sidebar mini show  'sidebar-mini' - 1024px , "sidebar-mini-md" - 768px,
    | "sidebar-mini-xs" - always
    |--------------------------------------------------------------------------
    | Font sizes "text-sm"
    |--------------------------------------------------------------------------
    | See https://adminlte.io/themes/v3/# - right top corner "Customize AdminLTE"
    */
    'body_default_class' => 'hold-transition sidebar-mini sidebar-open',

    /*
    |--------------------------------------------------------------------------
    | Admin Logo
    |--------------------------------------------------------------------------
    |
    | Displayed in navigation panel.
    |
    */

    'logo' => '<svg width="60" height="50" viewBox="0 0 261 119" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M58.1678 63.1298L58.1054 34.1884L43.7595 58.1399H40.1418L25.7959 34.5626V63.1298H18.0615V19.4682H24.7355L42.0754 48.4096L59.1034 19.4682H65.7774L65.8397 63.1298H58.1678Z" fill="white"/><path d="M105.951 53.0252H84.1201L79.8163 63.1298H71.4582L91.1059 19.4682H99.0897L118.8 63.1298H110.317L105.951 53.0252ZM103.269 46.6631L95.0355 27.5768L86.8645 46.6631H103.269Z" fill="white"/><path d="M131.017 26.3293H116.546V19.4682H153.596V26.3293H139.125V63.1298H131.017V26.3293Z" fill="white"/><path d="M179.281 63.7535C174.874 63.7535 170.882 62.7971 167.306 60.8843C163.771 58.9299 160.985 56.2479 158.947 52.8381C156.952 49.4284 155.954 45.582 155.954 41.299C155.954 37.016 156.972 33.1696 159.01 29.7599C161.047 26.3501 163.833 23.6888 167.368 21.776C170.944 19.8217 174.936 18.8445 179.344 18.8445C182.92 18.8445 186.184 19.4682 189.136 20.7157C192.089 21.9632 194.584 23.772 196.621 26.1422L191.382 31.0697C188.221 27.66 184.334 25.9551 179.718 25.9551C176.724 25.9551 174.042 26.6204 171.672 27.951C169.301 29.2401 167.451 31.0489 166.12 33.3775C164.79 35.7062 164.124 38.3466 164.124 41.299C164.124 44.2513 164.79 46.8918 166.12 49.2204C167.451 51.5491 169.301 53.3787 171.672 54.7093C174.042 55.9984 176.724 56.6429 179.718 56.6429C184.334 56.6429 188.221 54.9172 191.382 51.4659L196.621 56.4558C194.584 58.826 192.068 60.6348 189.074 61.8823C186.122 63.1298 182.857 63.7535 179.281 63.7535Z" fill="white"/><path d="M243.187 19.4682V63.1298H235.078V44.4177H212.499V63.1298H204.391V19.4682H212.499V37.4942H235.078V19.4682H243.187Z" fill="white"/><path d="M28.8553 98.3224C29.9425 99.3616 30.4861 100.899 30.4861 102.938V112.803H28.4681V110.322C27.993 111.15 27.2949 111.794 26.3758 112.259C25.4568 112.721 24.3637 112.955 23.0985 112.955C21.3582 112.955 19.9737 112.532 18.9452 111.684C17.9166 110.837 17.4023 109.718 17.4023 108.325C17.4023 106.975 17.8814 105.884 18.8415 105.058C19.7997 104.23 21.3288 103.818 23.4251 103.818H28.3801V102.848C28.3801 101.476 28.0047 100.433 27.2519 99.7167C26.499 99.0006 25.402 98.6416 23.9589 98.6416C22.9695 98.6416 22.0211 98.8071 21.1098 99.1402C20.1986 99.4733 19.4184 99.9321 18.7653 100.516L17.8169 98.9128C18.6069 98.2267 19.5572 97.6981 20.666 97.3251C21.7747 96.9522 22.9401 96.7647 24.1662 96.7647C26.2057 96.7627 27.7661 97.2833 28.8553 98.3224ZM26.4814 110.426C27.332 109.871 27.9656 109.069 28.3801 108.02V105.417H23.4857C20.8146 105.417 19.481 106.367 19.481 108.262C19.481 109.189 19.8271 109.921 20.5193 110.456C21.2115 110.99 22.1814 111.257 23.427 111.257C24.612 111.257 25.6308 110.982 26.4814 110.426Z" fill="white"/><path d="M50.7171 96.8855V110.866C50.7171 113.568 50.0679 115.571 48.7734 116.873C47.477 118.174 45.5255 118.826 42.913 118.826C41.4699 118.826 40.0992 118.609 38.8047 118.176C37.5082 117.743 36.4562 117.141 35.6447 116.375L36.7123 114.741C37.4632 115.427 38.3784 115.962 39.4558 116.345C40.5333 116.728 41.6654 116.921 42.8524 116.921C44.8293 116.921 46.2842 116.453 47.213 115.513C48.1418 114.576 48.6072 113.118 48.6072 111.141V109.112C47.9541 110.122 47.0996 110.888 46.0397 111.412C44.9799 111.937 43.8106 112.2 42.5239 112.2C41.0593 112.2 39.7296 111.871 38.5329 111.217C37.3361 110.562 36.3975 109.643 35.7151 108.462C35.0326 107.281 34.6924 105.945 34.6924 104.453C34.6924 102.961 35.0346 101.629 35.7151 100.46C36.3975 99.289 37.3322 98.3815 38.5192 97.7352C39.7042 97.089 41.0417 96.7678 42.5239 96.7678C43.8497 96.7678 45.0464 97.0391 46.1141 97.5856C47.1817 98.1301 48.0421 98.916 48.6952 99.9452V96.8895H50.7171V96.8855ZM45.7934 109.549C46.7026 109.055 47.4105 108.364 47.915 107.475C48.4195 106.587 48.6718 105.578 48.6718 104.449C48.6718 103.32 48.4195 102.317 47.915 101.437C47.4105 100.56 46.7085 99.8734 45.809 99.3807C44.9095 98.8861 43.8849 98.6388 42.739 98.6388C41.6107 98.6388 40.5978 98.8821 39.6983 99.3648C38.7988 99.8495 38.0968 100.536 37.5923 101.423C37.0878 102.311 36.8355 103.32 36.8355 104.449C36.8355 105.578 37.0878 106.587 37.5923 107.475C38.0968 108.362 38.7988 109.055 39.6983 109.549C40.5978 110.044 41.6127 110.291 42.739 110.291C43.8653 110.291 44.8821 110.046 45.7934 109.549Z" fill="white"/><path d="M70.2142 105.51H57.1597C57.279 107.164 57.9008 108.502 59.0291 109.519C60.1574 110.537 61.5809 111.047 63.3017 111.047C64.2716 111.047 65.1613 110.872 65.9728 110.517C66.7843 110.164 67.4844 109.643 68.0788 108.959L69.2658 110.351C68.5736 111.199 67.7093 111.845 66.669 112.288C65.6287 112.731 64.4887 112.952 63.2431 112.952C61.6416 112.952 60.2219 112.605 58.9861 111.909C57.7502 111.213 56.7862 110.249 56.094 109.019C55.4018 107.788 55.0557 106.396 55.0557 104.842C55.0557 103.288 55.3861 101.896 56.049 100.665C56.7119 99.4347 57.6212 98.4773 58.7788 97.7912C59.9364 97.105 61.2348 96.762 62.6799 96.762C64.123 96.762 65.4194 97.105 66.5653 97.7912C67.7112 98.4773 68.6127 99.4307 69.2658 100.651C69.9169 101.872 70.2435 103.268 70.2435 104.842L70.2142 105.51ZM58.8942 100.077C57.8754 101.056 57.2966 102.331 57.1578 103.905H68.2235C68.0847 102.331 67.5059 101.054 66.4891 100.077C65.4703 99.0996 64.1993 98.609 62.676 98.609C61.1742 98.611 59.913 99.0996 58.8942 100.077Z" fill="white"/><path d="M87.1653 98.5031C88.3229 99.664 88.9017 101.353 88.9017 103.573V112.802H86.7957V103.785C86.7957 102.129 86.389 100.869 85.5794 100.003C84.7679 99.1354 83.6103 98.7006 82.1085 98.7006C80.4288 98.7006 79.0972 99.2112 78.1175 100.23C77.1398 101.25 76.649 102.656 76.649 104.451V112.804H74.543V96.8875H76.561V99.8236C77.1339 98.8562 77.9298 98.1042 78.9486 97.5697C79.9673 97.0351 81.1484 96.7678 82.4938 96.7678C84.4511 96.7639 86.0077 97.3443 87.1653 98.5031Z" fill="white"/><path d="M97.0163 111.925C95.8 111.239 94.8458 110.275 94.1536 109.035C93.4613 107.794 93.1152 106.396 93.1152 104.842C93.1152 103.288 93.4613 101.896 94.1536 100.665C94.8458 99.4347 95.8 98.4773 97.0163 97.7912C98.2326 97.105 99.6131 96.762 101.156 96.762C102.501 96.762 103.702 97.0292 104.76 97.5638C105.818 98.0983 106.653 98.8802 107.267 99.9094L105.695 100.998C105.18 100.211 104.527 99.6222 103.737 99.2273C102.945 98.8343 102.085 98.6369 101.156 98.6369C100.028 98.6369 99.0148 98.8942 98.1153 99.4088C97.2158 99.9234 96.5138 100.655 96.0093 101.603C95.5048 102.552 95.2525 103.631 95.2525 104.842C95.2525 106.073 95.5048 107.158 96.0093 108.095C96.5138 109.035 97.2158 109.759 98.1153 110.273C99.0148 110.788 100.03 111.045 101.156 111.045C102.085 111.045 102.945 110.854 103.737 110.469C104.527 110.086 105.18 109.501 105.695 108.714L107.267 109.803C106.653 110.832 105.812 111.614 104.744 112.148C103.677 112.683 102.478 112.95 101.154 112.95C99.6112 112.954 98.2326 112.611 97.0163 111.925Z" fill="white"/><path d="M124.264 96.8845L116.462 114.71C115.828 116.202 115.097 117.261 114.266 117.888C113.435 118.514 112.436 118.825 111.268 118.825C110.517 118.825 109.815 118.703 109.162 118.462C108.511 118.221 107.946 117.858 107.471 117.373L108.45 115.769C109.24 116.575 110.191 116.98 111.297 116.98C112.009 116.98 112.617 116.779 113.122 116.374C113.626 115.971 114.096 115.285 114.532 114.317L115.214 112.773L108.243 96.8865H110.439L116.313 110.414L122.187 96.8865H124.264V96.8845Z" fill="white"/><path d="M260.84 19.1778H256.929V4.63324H4.75071V19.1778H0.839844V0.644043H260.84V19.1778Z" fill="white"/><path d="M260.84 82.3688H0.839844V63.835H4.75071V78.3796H256.929V63.835H260.84V82.3688Z" fill="white"/></svg>',

    /*
    |--------------------------------------------------------------------------
    | Admin URL prefix
    |--------------------------------------------------------------------------
    */

    'url_prefix' => 'admin',

    /*
     * Subdomain & Domain support routes
     */
    'domain'     => false,

    /*
    |--------------------------------------------------------------------------
    | Middleware to use in admin routes
    |--------------------------------------------------------------------------
    |
    | In order to create authentication views and routes
    | don't forget to execute `php artisan make:auth`.
    | See https://laravel.com/docs/authentication
    |
    */

    'middleware'               => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    */

    'favicon' => '/packages/sleepingowl/default/images/favicon.ico',

    /*
    |--------------------------------------------------------------------------
    | Load dev Vue js
    |--------------------------------------------------------------------------
    */

    'dev_assets' => env('ADMIN_DEV_ASSETS', false),

    /*
    |--------------------------------------------------------------------------
    | Env Editor
    |--------------------------------------------------------------------------
    | Url for env editor
    |
    */
    'env_editor_url'           => 'env/editor',

    /*
     * Excluded keys
     */
    'env_editor_excluded_keys' => [
        'APP_KEY', 'DB_*',
    ],

    /*
     * Env editor middlewares
     */
    'env_editor_middlewares'   => [],

    /*
     * Enable and show link in navigation
     * 'show_editor' is @deprecated
     */
    'enable_editor'            => false,
    'env_keys_readonly'        => false,
    'env_can_delete'           => true,
    'env_can_add'              => true,

    /*
     * --------------------------------------------------------------------------
     * Add your policy class here.
     * --------------------------------------------------------------------------
     */
    'env_editor_policy'        => '',

    /*
     * --------------------------------------------------------------------------
     * DataTables state saving.
     * --------------------------------------------------------------------------
     */
    'state_datatables'         => true,

    /*
     * --------------------------------------------------------------------------
     * Tabs state remember.
     * --------------------------------------------------------------------------
     */
    'state_tabs'               => false,

    /*
     * --------------------------------------------------------------------------
     * Filters state remember in DataTables.
     * --------------------------------------------------------------------------
     */
    'state_filters'            => false,

    /*
    |--------------------------------------------------------------------------
    | Authentication default provider
    |--------------------------------------------------------------------------
    |
    | @see config/auth.php : providers
    |
    */

    'auth_provider' => 'admins',

    /*
    |--------------------------------------------------------------------------
    |  Path to admin bootstrap files directory
    |--------------------------------------------------------------------------
    |
    | Default: app_path('Admin')
    |
    */

    'bootstrapDirectory' => app_path('Admin'),

    /*
    |--------------------------------------------------------------------------
    |  Directory for uploaded images (relative to `public` directory)
    |--------------------------------------------------------------------------
    */

    'imagesUploadDirectory' => 'images/uploads',

    /*
    |--------------------------------------------------------------------------
    |  Use LazyLoad for AdminColumn::image in tables
    |  in `imageLazyLoadFile` insert path to file or `data:image/gif;base64,...`
    |--------------------------------------------------------------------------
    */

    'imageLazyLoad'     => false,
    'imageLazyLoadFile' => 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==',

    /*
    |--------------------------------------------------------------------------
    |  Allowed Extensions for uploaded images - array
    |--------------------------------------------------------------------------
    */

    'imagesAllowedExtensions' => [
        'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp',
        'ico', 'jpe',
    ],

    /*
    |--------------------------------------------------------------------------
    |  Allow to upload svg-files without required xml-header as image - boolean
    |--------------------------------------------------------------------------
    */

    'imagesAllowSvg' => false,

    /*
    |--------------------------------------------------------------------------
    |  Behavoir if file exists (default 'UPLOAD_HASH'). See in UploadController
    |--------------------------------------------------------------------------
    */

    'imagesUploadFilenameBehavior' => 'UPLOAD_HASH',

    /*
    |--------------------------------------------------------------------------
    |  Directory for uploaded files (relative to `public` directory)
    |--------------------------------------------------------------------------
    */

    'filesUploadDirectory' => 'files/uploads',

    /*
    |--------------------------------------------------------------------------
    |  Allowed Extensions for uploaded files - array
    |--------------------------------------------------------------------------
    */

    'filesAllowedExtensions' => [],

    /*
    |--------------------------------------------------------------------------
    |  Behavoir if file exists (default 'UPLOAD_HASH'). See in UploadController
    |--------------------------------------------------------------------------
    */

    'filesUploadFilenameBehavior' => 'UPLOAD_HASH',

    /*
    |--------------------------------------------------------------------------
    |  Admin panel template
    |--------------------------------------------------------------------------
    */

    'template' => SleepingOwl\Admin\Templates\TemplateDefault::class,

    /*
    |--------------------------------------------------------------------------
    |  Default date and time formats
    |--------------------------------------------------------------------------
    */

    'datetimeFormat' => 'Y-m-d H:i',
    'dateFormat'     => 'Y-m-d',
    'timeFormat'     => 'H:i',
    'timezone'       => 'Europe/Moscow',

    /*
    |--------------------------------------------------------------------------
    | Use Card
    |--------------------------------------------------------------------------
    |
    | Using default cards views.
    |
    */

    'useWysiwygCard'    => false,
    'useRelationCard'    => false,
    'useHasManyLocalCard'    => false,

    /*
    |--------------------------------------------------------------------------
    | Editors
    |--------------------------------------------------------------------------
    |
    | Select default editor and tweak options if needed.
    |
    */

    'wysiwyg'                => [
        'default'    => 'ckeditor',

        /*
         * See http://docs.ckeditor.com/#!/api/CKEDITOR.config
         */
        'ckeditor'   => [
            'defaultLanguage' => config('app.locale'),
            'height'          => 200,
            'allowedContent'  => true,
            'extraPlugins'    => 'uploadimage,image2,justify,youtube,uploadfile',
            /*
             * WARNING!!!! CKEDITOR on D & D and UploadImageDialog
             * BY DEFAULT IMAGES WILL STORE TO imagesUploadDirectory = /images/uploads
             * 'uploadUrl'            => '/path/to/your/action',
             * 'filebrowserUploadUrl' => '/path/to/your/action',
             */

        ],

        /*
         * See https://www.tinymce.com/docs/
         */
        'tinymce'    => [
            'height' => 200,
        ],

        /*
         * See https://github.com/NextStepWebs/simplemde-markdown-editor
         */
        'simplemde'  => [
            'hideIcons' => ['side-by-side', 'fullscreen'],
        ],

        /*
        * ver. 0.8.12
        * See https://summernote.org/
        * Need jQuery
        */
        'summernote' => [
            'height'     => 200,
            'lang'       => 'ru-RU',
            'codemirror' => [
                'theme' => 'monokai',
            ],
        ],

        /*
         * See https://ckeditor.com/docs/ckeditor5/latest/builds/guides/integration/configuration.html
         *
         * For using CKFinder with CKEditor 5 you must load additional js-file, see /app/Admin/bootstrap.php
         * See https://ckeditor.com/docs/ckeditor5/latest/features/image-upload/ckfinder.html#configuring-the-full-integration
         *
         * Be careful: CKEditor 5 haven't html source code button feature!
         * See https://github.com/ckeditor/ckeditor5/issues/592
         */
        'ckeditor5'  => [
            'files' => [
                /*
                 * Use Classic build from CDN - provides a limited number of components and capabilities
                 * See https://ckeditor.com/ckeditor-5/download/
                 */
                //'editor' => '//cdn.ckeditor.com/ckeditor5/27.1.0/classic/ckeditor.js',
                //'translation' => '//cdn.ckeditor.com/ckeditor5/27.1.0/classic/translations/'.config('app.locale').'.js',
                /*
                 * Use Custom build with most-used additional plugins
                 * See https://ckeditor.com/ckeditor-5/online-builder/
                 */
                'editor'      => '/packages/sleepingowl/ckeditor5/build/ckeditor.js',
                'translation' => '/packages/sleepingowl/ckeditor5/build/translations/'.config('app.locale').'.js',
            ],

            'language'      => config('app.locale'),

            // Text alignment options
            'alignment'     => [
                'options' => [
                    'left', 'center', 'right', /*'justify',*/
                ],
            ],

            // Uncomment some plugins if you need to enable them
            'removePlugins' => [
                // See https://ckeditor.com/docs/ckeditor5/latest/api/module_heading_title-Title.html
                'Title',
                // See https://ckeditor.com/docs/ckeditor5/latest/features/lists/lists.html#list-styles
                'ListStyle',
                // See https://ckeditor.com/docs/ckeditor5/latest/features/markdown.html
                'Markdown',
            ],

            // Toolbar components
            'toolbar'       => [
                // Active toolbar components
                'undo', 'redo', '|',
                'heading', '|',
                'bold', 'italic', 'alignment', 'fontColor', 'blockQuote', 'link', 'bulletedList', 'numberedList', 'removeFormat', '|',
                'insertImage', 'mediaEmbed', 'insertTable', '|',

                // All available toolbar components:
                /*
                'heading',
                'bold', 'italic', 'underline', 'strikethrough', 'subscript', 'superscript',
                'alignment', 'fontFamily', 'fontSize', 'fontColor', 'fontBackgroundColor', 'highlight',
                'link', 'bulletedList', 'numberedList', 'outdent', 'indent',
                'imageUpload', 'imageInsert', 'mediaEmbed', 'insertTable', 'blockQuote', 'htmlEmbed',
                'textPartLanguage', 'codeBlock', 'code', 'pageBreak', 'horizontalLine', 'specialCharacters',
                'undo', 'redo', 'removeFormat', '|',
                */
            ],

            // Images options
            'image'         => [
                'styles'  => [
                    'alignLeft', 'alignCenter', 'alignRight', 'full', 'side',
                ],
                'toolbar' => [
                    'imageStyle:alignLeft', 'imageStyle:alignCenter', 'imageStyle:alignRight', '|',
                    'imageTextAlternative', '|', 'link',
                ],
            ],

            // Tables options
            'table'         => [
                'contentToolbar' => [
                    'tableColumn', 'tableRow', 'mergeTableCells', 'tableProperties', 'tableCellProperties',
                ],
            ],

            // Media embed options
            'mediaEmbed'    => [
                'toolbar'         => ['mediaEmbed'],
                /**
                 * @see https://ckeditor.com/docs/ckeditor5/latest/features/media-embed.html#including-previews-in-data
                 * @see https://ckeditor.com/docs/ckeditor5/latest/features/media-embed.html#displaying-embedded-media-on-your-website
                 */
                'previewsInData'  => true,
                /**
                 * The names of providers with rendering functions (previews): dailymotion, spotify, youtube, vimeo.
                 *
                 * @see https://ckeditor.com/docs/ckeditor5/latest/api/module_media-embed_mediaembed-MediaEmbedConfig.html#member-providers
                 *
                 * So, we need to remove providers without rendering function
                 * @see https://ckeditor.com/docs/ckeditor5/latest/features/media-embed.html#removing-media-providers
                 */
                'removeProviders' => ['instagram', 'twitter', 'googleMaps', 'flickr', 'facebook'],
            ],

            'uploadUrl'            => '/storage/images_admin',
            'filebrowserUploadUrl' => '/storage/images_admin',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | DataTables
    |--------------------------------------------------------------------------
    |
    | Select default settings for datatable
    |
    */
    'datatables'             => [],

    /*
    |--------------------------------------------------------------------------
    | DataTables column highlight
    |--------------------------------------------------------------------------
    |
    | Highlight DataTables column on mouseover
    |
    */
    'datatables_highlight'   => false,

    /*
    |--------------------------------------------------------------------------
    | Breadcrumbs
    |--------------------------------------------------------------------------
    |
    */
    'breadcrumbs'            => true,

    /*
    |--------------------------------------------------------------------------
    | Autoupdate datatables
    |--------------------------------------------------------------------------
    |
    | Interval in minutes. Do not set too low.
    | dt_autoupdate_interval >= 1 and (int)
    | dt_autoupdate_class - custom class if need (can be null)
    | dt_autoupdate_color - color ProgressBar (can be null)
    |
    */
    'dt_autoupdate'          => false,
    'dt_autoupdate_interval' => 5, //minutes
    'dt_autoupdate_class'    => '',
    'dt_autoupdate_color'    => '#dc3545',

    /*
    |--------------------------------------------------------------------------
    | Add scrolls button
    |--------------------------------------------------------------------------
    |
    */

    'scroll_to_top'    => true,
    'scroll_to_bottom' => true,

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started.
    |
    */

    'aliases' => [
        // Components
        'Assets'              => KodiCMS\Assets\Facades\Assets::class,
        'PackageManager'      => KodiCMS\Assets\Facades\PackageManager::class,
        'Meta'                => KodiCMS\Assets\Facades\Meta::class, // will destroy
        'Form'                => Collective\Html\FormFacade::class,
        'HTML'                => Collective\Html\HtmlFacade::class,
        'WysiwygManager'      => SleepingOwl\Admin\Facades\WysiwygManager::class,
        'MessagesStack'       => SleepingOwl\Admin\Facades\MessageStack::class,

        // Presenters
        'AdminSection'        => SleepingOwl\Admin\Facades\Admin::class,
        'AdminTemplate'       => SleepingOwl\Admin\Facades\Template::class,
        'AdminNavigation'     => SleepingOwl\Admin\Facades\Navigation::class,
        'AdminColumn'         => SleepingOwl\Admin\Facades\TableColumn::class,
        'AdminColumnEditable' => SleepingOwl\Admin\Facades\TableColumnEditable::class,
        'AdminColumnFilter'   => SleepingOwl\Admin\Facades\TableColumnFilter::class,
        'AdminDisplayFilter'  => SleepingOwl\Admin\Facades\DisplayFilter::class,
        'AdminForm'           => SleepingOwl\Admin\Facades\Form::class,
        'AdminFormElement'    => SleepingOwl\Admin\Facades\FormElement::class,
        'AdminDisplay'        => SleepingOwl\Admin\Facades\Display::class,
        'AdminWidgets'        => SleepingOwl\Admin\Facades\Widgets::class,
    ],
];
