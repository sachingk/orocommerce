define(function(require) {
    'use strict';

    var ShoppingListCollectionComponent;
    var BaseComponent = require('oroui/js/app/components/base/component');
    var BaseCollection = require('oroui/js/app/models/base/collection');
    var ShoppingListCollectionService = require('oroshoppinglist/js/shoppinglist-collection-service');
    var mediator = require('oroui/js/mediator');
    var _ = require('underscore');

    ShoppingListCollectionComponent = BaseComponent.extend({
        /**
         * @inheritDoc
         */
        listen: {
            'shopping-list:line-items:update-response mediator': '_onLineItemsUpdate'
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.collection = new BaseCollection(options.shoppingLists);
            this.collection.comparator = this.comparator;

            this.collection.on('update', function(collection, options) {
                if (options.add) {
                    mediator.trigger('shopping-list:refresh');
                }
            });
            this.collection.on('change', function(options) {
                if (options && options.refresh) {
                    mediator.trigger('shopping-list:refresh');
                }
            });
            ShoppingListCollectionService.shoppingListCollection.resolve(this.collection);
        },

        comparator: function(model) {
            return model.get('id');
        },

        _onLineItemsUpdate: function(model, response) {
            if (!model || !response) {
                return;
            }

            if (response.message) {
                mediator.execute(
                    'showFlashMessage', (response.hasOwnProperty('successful') ? 'success' : 'error'),
                    response.message
                );
            }

            if (response.product) {
                model.set('shopping_lists', response.product.shopping_lists, {silent: true});
                model.trigger('change:shopping_lists');
            }

            if (response.shoppingList && !this.collection.find({id: response.shoppingList.id})) {
                this.collection.add(_.defaults(response.shoppingList, {is_current: true}), {
                    silent: true
                });
            }

            this.collection.trigger('change', {
                refresh: true
            });
        }
    });

    return ShoppingListCollectionComponent;
});
