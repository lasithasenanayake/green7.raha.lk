WEBDOCK.component().register(function(exports){

    var bindData = {
        items: [
            {
                href : "#/admin-uom",
                imgSrc:  "assets/mylunch/img/admin/uom.png",
                name: "UOM",
                description : "Manage the Unit of Meature"

            },
            {
                href : "#/admin-allcategories",
                imgSrc:  "assets/mylunch/img/admin/categorymanage.png",
                name: "Product Categories",
                description : "Manage all the products categories"

            },
            {
                href : "#/admin-allproducts",
                imgSrc:  "assets/mylunch/img/admin/prodmanage.png",
                name: "Products",
                description : "Manage all the products in the inventory"

            },
            {
                href : "#/admin-allstores",
                imgSrc:  "assets/mylunch/img/admin/store.png",
                name: "Stores",
                description : "Manage Stores"

            },
            {
                href : "#/admin-allriders",
                imgSrc:  "assets/mylunch/img/admin/rider.png",
                name: "Riders",
                description : "Manage all the riders in the company"

            },
            {
                href : "#/admin-grn",
                imgSrc:  "assets/mylunch/img/admin/grn.jpg",
                name: "GRN",
                description : "Create/View GRN"

            },
            {
                href : "#/admin-inventory",
                imgSrc:  "assets/mylunch/img/admin/inventory.png",
                name: "Inventory",
                description : "Manage all the products in the inventory"

            },
            {
                href : "#/admin-orders",
                imgSrc:  "assets/mylunch/img/admin/order.png",
                name: "Orders",
                description : "Manage Orders"

            }
        ]
    }

    var vueData =  {
        data : bindData,
        onReady: function(app){
            $("body").css("background-image","none");
        },
        methods:{

        }
    }

    exports.vue = vueData;
    exports.onReady = function(){
    }
});