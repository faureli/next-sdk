<?php
namespace XLiteWeb\tests;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of testLogin
 *
 * @author givi
 */
class testCategories extends \XLiteWeb\AXLiteWeb{
    
    /**
     * @dataProvider provider
     */
    public function testAddCategory($dataset) {
        
        $categories = $this->getPage('Admin\Categories');
        $this->assertTrue($categories->load(true), 'Error loading categories page.');
        $this->assertTrue($categories->validate(),'Loaded page is not categories page.');
        
        $categories->NewCategory();
        $categories->inputCategoryName($dataset['testData']['name']);
        $categories->SaveChanges();
        
        $this->assertEquals($dataset['testData']['name'],$categories->getLastCategoryName(),'Category name does not match');
        $categoryId = $categories->getLastCategoryId();
        $this->assertGreaterThan(0, $categoryId, 'Error geting last categoryId');
        
        $categories->editCategory($categoryId);
        
        $categoryUpdate = $this->getPage('Admin\CategoryUpdate');
        $categoryUpdate->inputAvailability($dataset['testData']['availability']);
        $categoryUpdate->inputDescription($dataset['testData']['description']);
        $categoryUpdate->inputMeta_title($dataset['testData']['meta_title']);
        $categoryUpdate->inputMeta_keywords($dataset['testData']['meta_keywords']);
        $categoryUpdate->inputMeta_desc($dataset['testData']['meta_desc']);
        $categoryUpdate->inputMemberships($dataset['testData']['memberships']);
        
        $categoryUpdate->Update();
        
        //TODO: добавить проверку полей после сохранения
        
        $storeFront = $this->getPage('Customer\Index');
        $storeFront->load();
        $this->assertTrue($storeFront->validate(), 'Storefront is inaccessible.');
        
        $categoryLink = $storeFront->categoriesBox_getLink($dataset['testData']['name']);
        if ($dataset['results']['availInStorefront']) {
            $this->assertNotFalse($categoryLink, 'Category link not present.');
            $categoryLink->click();
        
            $category = $this->getPage('Customer\Category');
            $this->assertEquals($dataset['testData']['name'], $category->pageTitle()->getText(), 'Page title does not match category name.');
        } else {
            $this->assertFalse($categoryLink, 'Disabled Category link present.');
        }
        
        $this->assertTrue($categories->load(true), 'Error loading categories page.');
        $this->assertTrue($categories->validate(),'Loaded page is not categories page.');
        
        $categories->deleteCategory($categoryId);
        $categories->SaveChanges();
    }
    
    public function provider()
    {
        $datasets = array();
        $datasets['Usual category'] = array(
            array(
            'config'=>array(),
            'testData'=>array(
                'name'          => 'Test category 1',
                'description'   => 'Description of test category 1!!!',
                'memberships'    => array(),//'No membership',
                'availability'  => 'Yes',
                'meta_title'    => 'Test category title',
                'meta_keywords' => 'test, category',
                'meta_desc'    => 'test category meta description',
            ),
            'results'=>array(
                'availInStorefront'=>true,
            )
        ));
        
        $datasets['Disabled category'] = array(
            array(
            'config'=>array(),
            'testData'=>array(
                'name'          => 'Test category 2',
                'description'   => 'Description <b>of</b> test category 2!!!',
                'memberships'    => array(),//'No membership',
                'availability'  => 'No',
                'meta_title'    => 'Test category title',
                'meta_keywords' => 'test, category',
                'meta_desc'    => 'test category meta description',
            ),
            'results'=>array(
                'availInStorefront'=>false,
            )
        ));
        
        $datasets['Category for "Platinum" membership'] = array(
            array(
            'config'=>array(),
            'testData'=>array(
                'name'          => 'Test category 3',
                'description'   => '',
                'memberships'    => array('2'),//'Platinum',
                'availability'  => 'Yes',
                'meta_title'    => 'Test category title',
                'meta_keywords' => 'test, category',
                'meta_desc'    => 'test category meta description',
            ),
            'results'=>array(
                'availInStorefront'=>false,
            )
        ));
              
        return $datasets;
    }
}