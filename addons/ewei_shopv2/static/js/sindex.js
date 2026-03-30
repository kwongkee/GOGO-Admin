// import _ from 'lodash';
 export default {
   name: 'manyAreaSelect',
   data: function () {
     return {
       chinaArea: JSON.parse(window.localStorage.getItem('chinaArea')) || [], // 这是地区联动的JSON
       notLimitButton: {
         notLimitCity: false,  // 城市不限
         notLimitDistrict: false,  // 地区不限
       },
       selectedAllButtonStatus: false, // 已选位置列表全部按钮的状态
       selectItem: {
         province: {},
         city: {},
         distric: {}
       },
       cityList: [], // 城市列表
       districList: [], // 区域列表
       rightDataList: [], // 选中项目组合成的渲染列表
       rightData: [], // 选中需要移除的
       leftData: [], // 左边选中的转发
     }
   },
   props: {
     selectedData: {
       type: [String, Object, Array]
     },
     iconDirection: {
       type: Object,
       default: function () { // 箭头图标
         return {
           left: 'fzicon fz-ad-you',
           right: 'fzicon fz-ad-right'
         }
       }
     }
   },
   computed: {
     selectedList () {  // 已选中列表
       if (this.selectedData && this.selectedData !== '') {
         this.rightDataList = this.selectedData;
         return this.rightDataList;
       } else {
         return this.rightDataList;
       }

     },
     direactionStatusToRight () { // 控制可以转移的箭头状态
       if (this.notLimitButton.notLimitCity || this.notLimitButton.notLimitDistrict) {
         if (this.notLimitButton.notLimitCity) {
           this.removeAllSelected(this.cityList);
           this.removeAllSelected(this.districList);
           return false;
         } else {
           if (this.notLimitButton.notLimitDistrict) {
             this.removeAllSelected(this.districList);
             return false;
           }
         }
         return false;
       } else {
         if (this.selectItem.distric.regionName) {
           return false;
         }
         return true;
       }
     },
     direactionStatusToLeft () { // 控制可以转移的箭头状态
       if (this.rightData.length === 0) {
         return true
       } else {
         return false
       }
     }
   },
   methods: {
     mapSelect (list, value, type) {  // 高亮选中
       if (type) {
         return list.map(pitem => {
           if (pitem.regionId === value.regionId) {
             if (value.selected && value.selected === true) {
               this.$delete(pitem, 'selected');
             } else {
               this.$set(pitem, 'selected', true)
             }
           }
         })
       } else {
         return list.map(pitem => {
           if (pitem.regionId === value.regionId) {
             if (value.selected && value.selected === true) {
               this.$delete(pitem, 'selected');
             } else {
               this.$set(pitem, 'selected', true)
             }
           } else {
             this.$delete(pitem, 'selected');
           }
         })
       }
     },
     resetToDefault () {
       this.leftData = []; // 清空需要转移的数组
       this.notLimitButton = {  // 重置按钮状态
         notLimitCity: false,  // 城市不限
         notLimitDistrict: false,  // 地区不限
       };
       this.selectItem.city = {};
       this.selectItem.distric = {}
       this.removeAllSelected(this.cityList);  // 清除选中状态
       this.removeAllSelected(this.districList); // 清除选中状态
       this.cityList = [];
       this.districList = [];
     },
     getCityList (item) {
       this.resetToDefault();
       if (item) {
         this.cityList = item.child;  // 获取城市列表
         this.selectItem.province = item;  // 保存省份对象
         this.mapSelect(this.chinaArea, item); // 高亮选择,单选
       }
     },
     getDistricList (item) {
       this.leftData = []; // 清空需要转移的数组
       this.notLimitButton.notLimitDistrict = false; // 重置按钮状态
       this.removeAllSelected(this.districList); // 清除选中状态
       this.selectItem.distric = {};
       this.districList = [];
       if (item) {
         this.districList = item.child; // 获取区域列表
         this.selectItem.city = item; // 保存省份对象
         this.mapSelect(this.cityList, item); // 高亮选择,单选
       }

     },
     getAreaCombineID (item) {  // 获取组合ID
       console.log(this.leftData)
       if (item) {
         this.selectItem.distric = item;
         this.mapSelect(this.districList, item, 'manySelect'); // 区域高亮选择,多选

         this.leftData.push({
           regionName: this.selectItem.province.regionName + '-' + this.selectItem.city.regionName + '-' + item.regionName,
           regionId: this.selectItem.province.regionId + '-' + this.selectItem.city.regionId + '-' + item.regionId
         })
         this.leftData = _.uniqBy(this.leftData, 'regionId');
         if (this.leftData.length === this.districList.length) {
           this.leftData = [];
           this.selectItem.distric = {};
           this.notLimitButton.notLimitDistrict = true; // 转为不限制地区
           this.leftData.push({
             regionName: this.selectItem.province.regionName + '-' + this.selectItem.city.regionName + '-不限',
             regionId: this.selectItem.province.regionId + '-' + this.selectItem.city.regionId + '-0'
           })
         }
       }

     },
     cityNotLitmit (item) {  // 城市不限
       this.leftData = []; // 请空数组
       this.selectItem.distric = {};
       this.removeAllSelected(this.districList);
       this.notLimitButton.notLimitCity = !this.notLimitButton.notLimitCity; // 不限按钮状态
       this.leftData.push({
         regionName: this.selectItem.province.regionName + '-不限-不限',
         regionId: this.selectItem.province.regionId + '-0-0'
       })
     },
     districNotLitmit (item) { // 区域不限
       this.leftData = []; // 请空数组
       this.notLimitButton.notLimitDistrict = !this.notLimitButton.notLimitDistrict; // 不限按钮状态
       this.leftData.push({
         regionName: this.selectItem.province.regionName + '-' + this.selectItem.city.regionName + '-不限',
         regionId: this.selectItem.province.regionId + '-' + this.selectItem.city.regionId + '-0'
       })
       if (!this.notLimitButton.notLimitDistrict) {
         this.leftData = [];
       }
     },
     transferToRight () { // 选中推入到已选中列表区域
       if (this.leftData && this.leftData.length !== 0) {
         if (this.leftData.length === 1) {  // 长度只有1,那就只有不限城市或者地区了
           let limitId = this.leftData[0].regionId.split('-');  // 比对比对,切割成数组
           this.rightDataList.map(item => {
             let id = item.regionId.split('-');
             if (limitId[0] === id[0]) {
               if (limitId[1] === '0') {  // 不限城市
                 this.rightDataList = this.rightDataList.filter(ritem => {
                   let rid = ritem.regionId.split('-');
                   if (limitId[0] !== rid[0]) {
                     return ritem;
                   }
                 })
               } else {
                 if (limitId[2] === '0') { // 不限地区
                   this.rightDataList = this.rightDataList.filter(ritem => {
                     let rid = ritem.regionId.split('-');
                     if ((limitId[0] === rid[0] && limitId[1] === rid[1])) {
                       if (ritem[2] === '0') {
                         return ritem;
                       }
                     } else {
                       if (limitId[0] !== rid[0] || limitId[1] !== rid[1]) {
                         return ritem;
                       }
                     }
                   })
                 } else {
                   this.rightDataList = this.rightDataList.filter(ritem => {
                     let rid = ritem.regionId.split('-');
                     if (limitId[0] === rid[0]) {
                       if (limitId[1] === rid[1]) {
                         if (!(rid[2] === '0')) {
                           return ritem;
                         }
                       } else {
                         if (!(rid[1] === '0')) {
                           return ritem
                         }
                       }
                     } else {
                       return ritem
                     }
                   })
                 }
               }

             }
           })
         } else {
           let limitId = this.leftData[0].regionId.split('-');  // 比对比对,切割成数组
           this.rightDataList = this.rightDataList.filter(ritem => {
             let rid = ritem.regionId.split('-');
             if (limitId[0] === rid[0]) {
               if (limitId[1] === rid[1]) {
                 if (!(rid[2] === '0')) {
                   return ritem;
                 }
               } else {
                 if (!(rid[1] === '0')) {
                   return ritem
                 }
               }
             } else {
               return ritem
             }
           })
         }
         this.leftData.map(item => {
           this.rightDataList.push(item);
         })
         this.rightDataList = _.uniqBy(this.rightDataList, 'regionId');
         this.resetToDefault();
       }


     },
     selectedAreaSingle (item) {  // 已选择区域单个选择
       if (item) {
         this.rightData = [];
         this.mapSelect(this.rightDataList, item, 'manySelect'); // 区域高亮选择,多选
         this.rightDataList.map(item => {
           if (item.selected) {
             this.rightData.push(item)
           }
         })
       }

     },
     selectedAllArea () { // 已选中区域全选反选
       if (this.selectedAllButtonStatus) {
         this.removeAllSelected(this.rightDataList);
         this.rightData = [];
       } else {
         this.rightDataList.map(item => this.$set(item, 'selected', true));
         this.rightData = this.rightDataList;
       }
       this.selectedAllButtonStatus = !this.selectedAllButtonStatus;
     },
     transferToLeft () {  // 从已选中列表区域退回待转发区域
       if (this.rightData && this.rightData.length !== 0) {
         this.rightDataList = this.rightDataList.filter(item => {
           if (!item.selected) {
             return item;
           }
         })
         this.rightData = [];
       }
     },
     removeAllSelected (list) {  // 清空选中状态
       list.map(item => this.$delete(item, 'selected'));
     }
   },
   watch: {
     'rightDataList' (newValue, oldValue) {  // 选择列表的值变动响应外部值的变动
       if (newValue.length !== this.rightData.length) {
         this.selectedAllButtonStatus = false;
       } else {
         if (newValue.length === 0) {
           this.selectedAllButtonStatus = false;
         } else {
           this.selectedAllButtonStatus = true;
         }
       }
       this.$emit('update:selectedData', newValue);
     }
   }
 }
</script>
