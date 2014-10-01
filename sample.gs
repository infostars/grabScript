
class grab
{
    Json config = {
        start_url: "http://ya.ru/",
        userAgent: "Curl 0.7",
        parserDriver: "grabtemplate"
    };

    List<object> queue;

    void onForeachCycleItem(object item)
    {
        ParsedItem item = (ParsedItem)item;

        // Пример объекта ParserItem
        Json sampleItemJson = {
            apiObject: "document",
            parser: {
                driver: "template",
                meta: {
                    "template_id": "__mongo_id_of_used_template__"
                }
            },
            date: "__when_was_parsed__",
            item: {
                post__external_id: 123,
                // ... etc
                hub__id: 1
            }
        };
        ParsedItem sampleItem = ParsedItem.fromJson(sampleItemJson);
    }

    void itemSave(ParsedItem item)
    {
        queue.push(item);
        ApiClient.save(item);
    }

    void onItemSaveFail(ParsedItem item, Error error)
    {
        Debug.dump(error, item);
    }

    void onParserSuccess(List<ParsedItem> items)
    {
        items.onEach = %call% itemSave;
        items.onFail = %call% onItemSaveFail;
        items.breakOnFail = false;
        items.execute();
    }

    void onParserFail(Error error)
    {
        Debug.dump(error);
    }

    void onProcessSuccess(string result, Request request)
    {
        Parser parser = new Parser();
        parser.content = result;
        parser.request = request;
        parser.driver = config.parserDriver;

        parser.onSuccess = %call% onParserSuccess;
    }

    void onProcessFail(Request request)
    {
        Debug.dump(request);
    }

    void process()
    {
        Request request = new Request();

        request.uri = config.start_url;

        request.userAgent = config.userAgent;
        request.userAgentGeneration = false;

        request.useProxy = true;

        // выбрать 1 вариант из 3-ех
        request.onSuccess = call onProcessSuccess;
        request.onSuccess = call(onProcessSuccess);
        request.onSuccess = new Callable(onProcessSuccess);

        request.onFail = call onProcessFail;
        request.onFail = call(onProcessFail);
        request.onFail = new Callable(onProcessFail);

        request.execute();

        Foreach foreachCycle = new Foreach();
        foreachCycle.items = queue;
        foreachCycle.onEachItem = %call% onForeachCycleItem;
        foreachCycle.execute();
    }
}
