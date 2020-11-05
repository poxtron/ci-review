/**
* External dependencies
*/
import React, { Component } from 'react';
import PureRenderMixin from 'react-addons-pure-render-mixin';

import isUndefined from 'lodash/isUndefined';
import assign from 'lodash/assign';
import get from 'lodash/get';
import isEmpty from 'lodash/isEmpty';

/**
* Internal dependencies
*/
import Utils from '../../../utils/utils';
import Hover from '../../../utils/hover-options';
import Responsive from '../../../utils/responsive-options';
import ETBuilderStore from '../../../stores/et-builder-store';
import ETBuilderModule from '../../et-builder-module';
import standardModulePropTypes from '../../../constants/standard-module-prop-types';
import getReinitAttrsList from '../../../constants/et-builder-module-reinit-attrs-common';
import {
  defaultClasses,
  componentWillMount,
  componentWillReceiveProps,
  inheritModuleClassName,
  addModuleClassName,
  removeModuleClassName,
  orderClassName,
  hideOnMobileClassName,
  moduleClassNameArray,
  moduleClassName,
  moduleID,
  globalSavingClass,
  globalModuleClass,
  linkRel,
} from '../../../mixins/et-builder-module-classes-mixin';
import {
  _shouldReinit,
  _updateLoadingStatus,
  _isDoneLoading
} from '../../../mixins/et-builder-module-ui-mixin';
import {
  quickAccessToggleId,
} from '../../quick-access/quick-access-properties';
import { withDynamicContent } from '../../../hoc/with-dynamic-content';
import { wpKses, renderContent } from '../../../lib/dynamic-content';
import MultiView from '../../../utils/multi-view/render';
import BackgroundLayout from '../../../utils/background-layout';

import './style.scss';

/**
* <ETBuilderModuleButton />
*/
class ETBuilderModuleButton extends Component {
  constructor(props) {
    super(props);

    /**
     * Bind PureRenderMixin
     */
    this.shouldComponentUpdate = PureRenderMixin.shouldComponentUpdate.bind(this);

    /**
     * Bind CSSModuleClassesMixin
     */
    this.defaultClasses = defaultClasses.bind(this);
    this.componentWillMount = componentWillMount.bind(this);
    this.componentWillReceiveProps = componentWillReceiveProps.bind(this);
    this.inheritModuleClassName = inheritModuleClassName.bind(this);
    this.addModuleClassName = addModuleClassName.bind(this);
    this.removeModuleClassName = removeModuleClassName.bind(this);
    this.orderClassName = orderClassName.bind(this);
    this.hideOnMobileClassName = hideOnMobileClassName.bind(this);
    this.moduleClassNameArray = moduleClassNameArray.bind(this);
    this.moduleClassName = moduleClassName.bind(this);
    this.moduleID = moduleID.bind(this);
    this.globalSavingClass = globalSavingClass.bind(this);
    this.globalModuleClass = globalModuleClass.bind(this);
    this.linkRel = linkRel.bind(this);

    /**
     * Bind ModuleUIMixin
     */
    this.reinitAttrsList = getReinitAttrsList('unifiedBackground');
    this.reinitAttrs = {};
    this._shouldReinit = _shouldReinit.bind(this);
    this._updateLoadingStatus = _updateLoadingStatus.bind(this);
    this._isDoneLoading = _isDoneLoading.bind(this);
  }

  getClassNames = (locked, alignment, hasButtonText, hasButtonUrl, buttonAlignments) => {
    const classNames = {
      et_pb_button_module_wrapper: true,
      et_pb_module: true,
      et_pb_empty_button: ! hasButtonText && ! hasButtonUrl,
      et_fb_locked_module: ( 'on' === locked ),
      [`${this.orderClassName()}_wrapper`]: true,
      et_pb_hovered: this.props.hovered,
    };

    if (!isUndefined(buttonAlignments.desktop) && !isEmpty(buttonAlignments.desktop)) {
      classNames[`et_pb_button_alignment_${buttonAlignments.desktop}`] = true;
    }

    if (!isUndefined(buttonAlignments.tablet) && !isEmpty(buttonAlignments.tablet)) {
      classNames[`et_pb_button_alignment_tablet_${buttonAlignments.tablet}`] = true;
    }

    if (!isUndefined(buttonAlignments.phone) && !isEmpty(buttonAlignments.phone)) {
      classNames[`et_pb_button_alignment_phone_${buttonAlignments.phone}`] = true;
    }

    return classNames;
  };

  componentDidMount() {
    /**
     * Update UI-related status
     */
    this._shouldReinit();
    this._updateLoadingStatus();
  }

  _renderText() {
    const text = MultiView.getDynamicByMode(this.props, 'button_text');

    if (text.loading) {
      return '...';
    }

    if (!Utils.hasValue(text.value)) {
      return this.props.dynamic.button_url.value;
    }

    if (text.dynamic) {
      return renderContent(wpKses(text.value));
    }

    return text.value;
  }

  render() {
    const additionalCss = [];
    const attrs     = this.props.attrs;
    const text      = MultiView.getDynamicByMode(this.props, 'button_text');
    const link      = this.props.dynamic.button_url;

    let buttonIcon       = null;
    let buttonIconTablet = null;
    let buttonIconPhone  = null;

    // Button Alignment.
    const buttonAlignmentValues = Responsive.getPropertyValues(attrs, 'button_alignment');
    const buttonAlignment       = get(buttonAlignmentValues, 'desktop', '');
    const buttonAlignmentTablet = get(buttonAlignmentValues, 'tablet', '');
    const buttonAlignmentPhone  = get(buttonAlignmentValues, 'phone', '');
    const buttonAlignments      = {
      desktop: Utils.getTextOrientation(buttonAlignment),
      tablet:  Utils.getTextOrientation(buttonAlignmentTablet),
      phone:   Utils.getTextOrientation(buttonAlignmentPhone),
    };

    /**
     * Module class name
     */
    this.removeModuleClassName('et_pb_module');

    // Background layout class names.
    const backgroundLayoutClassNames = BackgroundLayout.getBackgroundLayoutClass(attrs);
    this.addModuleClassName(backgroundLayoutClassNames);

    /**
     * Conditional button classes.
     */
    if (Utils.isOn(attrs.custom_button) && attrs.button_icon) {
      this.addModuleClassName('et_pb_custom_button_icon');
    }

    /**
     * Fetch icon.
     */
    if (attrs.button_icon && Utils.isOn(attrs.custom_button)) {
      const buttonIconValues = Responsive.getPropertyValues(attrs, 'button_icon');
      if (buttonIconValues.desktop) {
        buttonIcon = Utils.processFontIcon(buttonIconValues.desktop);
      }

      if (buttonIconValues.tablet) {
        buttonIconTablet = Utils.processFontIcon(buttonIconValues.tablet);
      }

      if (buttonIconValues.phone) {
        buttonIconPhone = Utils.processFontIcon(buttonIconValues.phone);
      }
    }

    const moduleProps = assign({
      tagName: 'a',
      moduleClassName: this.moduleClassNameArray(),
      additional_css: additionalCss,
    }, this.props);

    return (
      <ETBuilderModule
        data-icon={buttonIcon}
        data-icon-tablet={buttonIconTablet}
        data-icon-phone={buttonIconPhone}
        target={Utils.isOn(attrs.url_new_window) ? '_blank' : null}
        rel={this.linkRel('button')}
        wrapperClassName={this.getClassNames(attrs.locked, buttonAlignment, text.hasValue, link.hasValue, buttonAlignments)}
        href={link.value}
        {...moduleProps}
      >
        {this._renderText()}
      </ETBuilderModule>
    );
  }
}

ETBuilderModuleButton.propTypes = standardModulePropTypes;

export default withDynamicContent('et_pb_button')(ETBuilderModuleButton);
