var pageDC = new DataCollection();
pageDC.init(me.get('id'), 'CommunityPrograms-Hit');
pageDC.append('log', new Date().toISOString().slice(0, 10), false);