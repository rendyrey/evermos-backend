# evermos backend

# Facts
1. Our inventory quantities are often misreported, and some items even go as far as having a negative inventory quantity.
2. The misreported items are those that performed very well on our 12.12 event.
3. Because of these misreported inventory quantities, the Order Processing department was unable to fulfill a lot of orders, and thus requested help from our Customer Service department to call our customers and notify them that we have had to cancel their orders

# Do's
1. Describe what you think happened that caused those bad reviews during our 12.12 event and why it happened

Reviews coming from buyers can be specified in 3 types.
- Product Quality
- Service Quality
- Platform or User Experience on Using the application.


a large flash sale and major discounts likely and obviously attract customers to buy or just view our product more than daily basis. Therefore the quality of service will decrease due to large numbers of buyer that CS(s) have to handle.
These are few things that will be impacted by it:
- Customer Service become worse (chat & call responds interval). This happen because CS have to handle more customers than daily basis.
- Packing Quality (some customers perhaps got bad packing quality and it impacted the product) - Human error side
- Shipping Time (some buyers may get items with longer shipping time than estimated). This happen due to packages piled up full in the warehouse.
- User Experience become worse due to large traffic and the server can't handle it well.
- Human Error. some buyers perhaps got wrong package/item, color, incomplete items amount. This happen because of exhausted seller or others causes.
- etc.

2. Based on your analysis, propose a solution that will prevent the incidents from occurring again.

There are several reasons that could cause your inventory to be misreported.
If a customer has multiple platforms to sell, there must be a web service that can handle that. For example, there is a seller who has stores on 3 platforms, call it 'Bul', 'Tok' and 'Shop'. if a buyer purchases 1 item on one of the three platforms, the stock of goods must be reduced on all of these platforms.

But in this case I will assume it is on one platform only.

This is the process of the online purchasing that generally people experience including me:
1. Buyer choose the product
2. Buyer do the checkout
3. Buyer do the payment. -> this is where the inventory check have to be occur prior the payment to handle concurrent transaction.
4. Seller accept/process/cancel the transaction. -> this is where the inventory quantity have to decrease everytime seller accept/process the transaction.
5. Seller send the item.
6. Buyer receive the package
7. Buyer completed the transaction.

The solutions:
1. Listing of product should be shown only available/ready stock or we can use disabled to buy, hide button, or anything to prevent buyer buy the product that actually have zero stock.
2. The database design should be improve. the stock field ought to use UNSIGNED integer to prevent negative value.
3. Inventory decreasing have to be occurs when seller accept the transaction.
4. Inventory check have to be occurs prior the payment. If item has zero stock, the payment could be canceled. So the buyer got informed in advanced, not after the payment.
5. And lastly, on human side. The seller or CS has to recheck the items manually and update the quantity of product on the system regularly.
