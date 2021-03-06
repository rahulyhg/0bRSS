'use strict';

var ArticleLoader = new Class({
    feed: 12,
    page: 0,

    initialize: function (feed) {
        this.feed = feed;

        new Request.JSON({
            method: 'get',
            url: window.ZerobRSS.apiUri + '/v1/feeds/' + this.feed,
            onComplete: function (response) {
                window.ZerobRSS.ArticleLoader.unread = response.unread;

                window.ZerobRSS.ArticleLoader.getArticles();
            }
        }).send();
    },

    getArticles: function () {
        var readGet = null;

        if (this.unread > 0) {
            readGet = '&read=false';
        }



        new Request.JSON({
            method: 'get',
            url: window.ZerobRSS.apiUri + '/v1/feeds/' + this.feed + '/articles?page=' + this.page + readGet,
            onComplete: function (response) {
                var template = Handlebars.compile($('news-card-template').get('html'));

                response.each(function (article) {
                    var a = new Element('article');

                    a.set('data-id', article.identifier);
                    a.set('data-feed-id', article.feed_id);
                    a.set('html', template(article));

                    if (article.read) {
                        a.addClass('read');
                    }

                    a.addEvent('click', (function (e) {
                        window.ZerobRSS.ArticleLoader.clickArticle(e.target.getParent('article').get('data-id'));
                    }));

                    a.inject($('content'));
                });
            }
        }).send();
    },

    clickArticle: function (id) {
        window.ZerobRSS.Keyboard.currentArticle = id;

        $$('#content > article').each(function (elem) {
            elem.removeClass('active');
        });

        var article = $$('article[data-id=' + id + ']')[0];

        article.addClass('active');

        new Request.JSON({
            url: window.ZerobRSS.apiUri + '/v1/articles/' + id,
            data: JSON.encode({'read': true}),
            emulation: false,
            onComplete: function (response) {
                var wasRead = article.hasClass('read');

                article.addClass('read');

                if (!wasRead) {
                    var read = $$('#aside-menu > nav > a[data-feed-id=' + article.get('data-feed-id') + '] .unread')[0];

                    read.set('html', read.get('html') - 1);
                }
            }
        }).put();
    }
});
