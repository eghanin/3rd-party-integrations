<?php
namespace Emarsys\Emarsys\Block\Adminhtml\Customformfield\Edit\Renderer;

class CustomRenderer extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    public function getElementHtml()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $categoryFactory = $objectManager->create('Magento\Catalog\Model\CategoryFactory');
        $categoryModel = $objectManager->create('Magento\Catalog\Model\Category');
        $storeManager = $objectManager->create('Magento\Store\Model\StoreManagerInterface');
        $session = $objectManager->create('Magento\Backend\Model\Session');
        $optionArray = [];
        $selectedSubCats = "";
        $selectedCats = '';
        $html = "";
        $storeId = $session->getStore();
        $rootCategoryId = 0;
        $rootCategoryId = $storeManager->getStore($storeId)->getRootCategoryId();
        list($catTree, $selectedCats) = $this->getTreeCategories($rootCategoryId);
        $html = "
        <div class=\"emarsys-search\">
    <div class=\"category-multi-select\">
        <div class='admin__field'><div class='admin__field-control'>
     <div class='admin__action-multiselect-wrap action-select-wrap admin__action-multiselect-tree'>
<div class='admin__action-multiselect' id='selectedCategories'>$selectedCats</div></div></div>
</div>";
        $html .=
            " <ul><li>
        <div class= 'catg-sub-'><input type= 'checkbox' disabled = 'disabled'name= 'dummy-checkbox'/>Root Category</div>" .

            $catTree;
        //echo "<pre>"; echo $html; exit;
        $html .= "
<script>

function Unchecked(value) {
  document.getElementById('catCheckBox_'+value).click();
}

function categoryClick(value,catName) {
  if(document.getElementById('catCheckBox_'+value).checked == true)
      {
          document.getElementById('selectedCategories').innerHTML += '<span id=\''+value+'\' onclick=\'Unchecked('+value+')\' class=\"admin__action-multiselect-crumb\">'+catName+'<button class=\"action-close\" type=\"button\"><span class=\"action-close-text\"></span></button></span>';
      }
      else {
          document.getElementById(value).remove();

      }
}

</script>
<style>
        .category-multi-select ul{
            list-style: none;
            position: relative;
        }
        .category-multi-select ul li{
            position: relative;
        }
        .category-multi-select ul li ul{
            padding-left: 30px;
        }
        .category-multi-select ul li:after{
            border-top: 1px dashed #a79d95;
            height: 1px;
            top: 20px;
            width: 22px;
            content: '';
            left: -25px;
            position: absolute;
            }
        .category-multi-select>ul>li:after{
            border: none;
        }
        .category-multi-select ul li>div:before{
            border-left: 1px dashed #a79d95;
            bottom: 0;
            content: '';
            left: -25px;
            position: absolute;
            top: -10px;
            width: 1px;

        }
        .category-multi-select>ul>li>div:before{
            left: 5px;
            top: 25px;
        }
        .category-multi-select ul ul li>div:before{
                height: 30px;
        }
        .category-multi-select ul li>div{
            padding-top: 10px;
            padding-bottom: 10px;
        }
        .category-multi-select ul li>div input{
            position: relative;
            top:3px;
            margin-right: 10px;
        }


    </style>";
        return $html;
    }

    public function getTreeCategories($parentId, $level = 1)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $scopeConfig = $objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface');
        $html = '<ul class="category-' . $level . '">';
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $categoryFactory = $objectManager->create('Magento\Catalog\Model\CategoryFactory');
        $storeManager = $objectManager->create('Magento\Store\Model\StoreManagerInterface');
        $request = $objectManager->get('\Magento\Framework\App\Request\Http');
        $storeId = $request->getParam('store');
        $websiteId = $storeManager->getStore($storeId)->getWebsiteId();
        $allCategories = $categoryFactory->create()->getCollection()->addAttributeToFilter('parent_id', ['eq' => $parentId]);
        $categoryModel = $objectManager->create('Magento\Catalog\Model\Category');
        $selectedCats = '';
        $categoriesExcluded = $scopeConfig->getValue('emarsys_predict/feed_export/excludedcategories', 'websites', $websiteId);
        $categoriesExcluded = explode(',', $categoriesExcluded);
        foreach ($allCategories as $cat) {
            $checked = '';
            $category = $categoryModel->load($cat->getId());
            if (in_array($cat->getId(), $categoriesExcluded)) {
                $checked = 'checked=checked';
            }
            $level = $category->getLevel();
            $subcats = $category->getChildren();
            $html .= '<li class="catg-sub-$categoryLevel' . $category->getLevel() . '">';
            $html .= "<div class=\"catg-sub-$level \"><input type=\"checkbox\" $checked disabled=\"disabled\" name= \"checkbox\" onclick=\"categoryClick(this.value,'" . $category->getName() . "')\" id=\"catCheckBox_" . $category->getId() . "\" name=\"vehicle\" value=\"" . $category->getId() . "\">" . $category->getName() . "</div>";
            if ($checked != '') {
                $selectedCats .= '<span id="' . $category->getId() . '" onclick="Unchecked(' . $category->getId() . ')" class="admin__action-multiselect-crumb">' . $category->getName() . '</span>';
            }
            if (count(array_filter(explode(",", $subcats))) > 0) {
                list($catTree, $selectedSubCats) = $this->getTreeCategories($category->getId(), $level);
                $html .= $catTree;
                $selectedCats .= $selectedSubCats;
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        return [$html, $selectedCats];
    }
}
