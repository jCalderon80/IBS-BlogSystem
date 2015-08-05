window.onload = function () {
    tmce_sets = {
        selector: '#article-content',
        height: 400,
        width: '95%',
        plugins: ['advlist lists table autolink link image media preview lists spellchecker paste responsivefilemanager fullscreen'],
        menubar: false,
        toolbar2: 'responsivefilemanager formatselect bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | undo redo',
        toolbar1: 'styleselect table link unlink | spellchecker preview fullscreen',
        image_advtab: true,
        relative_urls: false,
        external_filemanager_path: '/admin/interface/filemanager/',  //PRODUCTION
        external_plugin: { 'filemanager': '/admin/interface/filemanager/plugin.min.js' }, //PRODUCTION
        //external_filemanager_path: '/test_environment/php/myPHPframe/admin/interface/filemanager/', //DEVELOPMENT
        //external_plugin: { 'filemanager': 'plugins/responsivefilemanager/plugin.min.js' }, //DEVELOPMENT
        filemanager_title: 'File Manager'
    };

    if (document.getElementById('article-content')) {
        tinymce.init(tmce_sets);
    }
};