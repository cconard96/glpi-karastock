# karastock GLPI plugin

Stock and order manager (reception, allocation and removal of stock) in order to have a visual as close as possible to the real of your stocks.
* For each order, define the supplier, the order date as well as the receipt and invoice date.
* Add the content (type, model, quantity and price) then assign them to a Support ticket and take it out of stock
* A visualization of the stock, overall, by type and / or model
* A quick visualization of the orders awaiting delivery thanks to the icons

## Explications - FR
Ce plugin a pour but de simplifier la gestion des commandes et du stock des articles commandés.
Une fois installé, bien penser à s'ajouter les droits sur le plugin, sinon le menu ou les icones ne s'afficherons pas correctement.

**Le point d'entrée de ce plugin est l'item "Commandes".**
L'ajout d'une commande va permettre d'y associer des articles.

Une fois la commande réceptionnée, on pourra attribuer les articles à des tickets ou a des équipements (devices) et enfin le sortir du stock.

Tant que l'article n'est pas sorti du stock, il apparaitra dans l'item "Stock"
Une fois sorti, il passera dans l'item "Historique" avec la possibilité d'en faire des exports CSV avec différentes colonnes ainsi que sur une plage de dates définie.

## Contributing

* Open a ticket for each bug/feature so it can be discussed
* Follow [development guidelines](http://glpi-developer-documentation.readthedocs.io/en/latest/plugins/index.html)
* Refer to [GitFlow](http://git-flow.readthedocs.io/) process for branching
* Work on a new branch on your own fork
* Open a PR that will be reviewed by a developer
