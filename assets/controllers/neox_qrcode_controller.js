import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
    static targets = [
        'content', 'preset', 'moduleShape', 'finderShape', 'alignmentShape', 'foreground', 'background',
        'finderColor', 'alignmentColor', 'gradientType', 'gradientTo', 'size', 'margin', 'moduleScale',
        'logoHref', 'finderIconHref', 'finderEffect', 'finderGradientTo', 'finderEyeShape', 'frameShape', 'frameLabel',
        'preview', 'status', 'presetPopup'
    ]

    static values = {
        endpoint: { type: String, default: '/api/qrcode/svg' },
        downloadEndpoint: { type: String, default: '/api/qrcode/png' },
        content: String,
        shape: String,
        preset: { type: String, default: '' },
    }

    connect() {
        if (this.hasPreviewTarget) {
            this.refresh()
        }

        this.element.dispatchEvent(new CustomEvent('neox:qrcode:ready', {
            bubbles: true,
            detail: { content: this.contentValue, shape: this.shapeValue },
        }))

        document.addEventListener('click', this._outsideClickHandler = (e) => {
            if (this.hasPresetPopupTarget && !this.element.contains(e.target)) {
                this.presetPopupTarget.hidden = true
            }
        })
    }

    disconnect() {
        if (this._outsideClickHandler) {
            document.removeEventListener('click', this._outsideClickHandler)
        }
    }

    togglePresets() {
        if (!this.hasPresetPopupTarget) return
        this.presetPopupTarget.hidden = !this.presetPopupTarget.hidden
    }

    selectPreset(e) {
        const btn = e.currentTarget
        const presetName = btn.dataset.preset
        const isUserPreset = btn.dataset.userPreset === '1'

        if (this.hasPresetPopupTarget) {
            this.presetPopupTarget.querySelectorAll('.neox-qrcode__popup-item').forEach(item => {
                item.classList.toggle('is-active', item === btn)
            })
            this.presetPopupTarget.hidden = true
        }

        if (isUserPreset) {
            const config = JSON.parse(btn.dataset.config || '{}')
            this._userPresetConfig = config
            this.presetValue = ''
        } else {
            this._userPresetConfig = null
            this.presetValue = presetName
        }

        this.refresh()
    }

    refresh() {
        clearTimeout(this.timer)
        this.timer = setTimeout(() => this.renderRemote(), 120)
    }

    async renderRemote() {
        if (!this.hasPreviewTarget) return
        this.setStatus('')

        try {
            const response = await fetch(this.endpointValue, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'image/svg+xml' },
                body: JSON.stringify(this.payload()),
            })

            if (!response.ok) {
                const text = await response.text()
                throw new Error(text || `HTTP ${response.status}`)
            }

            this.previewTarget.innerHTML = await response.text()
        } catch (error) {
            this.setStatus(error.message || 'Unable to render QR code.')
        }
    }

    payload() {
        const preset = this.presetValue || (this.hasPresetTarget ? this.presetTarget.value : '')
        const base = {
            content: this.hasContentTarget ? this.contentTarget.value : this.contentValue,
            preset: preset || null,
            size: this.hasSizeTarget ? Number(this.sizeTarget.value) : 320,
            margin: this.hasMarginTarget ? Number(this.marginTarget.value) : 4,
            moduleShape: this.hasModuleShapeTarget ? this.moduleShapeTarget.value : 'square',
            finderShape: this.hasFinderShapeTarget ? this.finderShapeTarget.value : 'square',
            foreground: this.hasForegroundTarget ? this.foregroundTarget.value : '#111111',
            background: this.hasBackgroundTarget ? this.backgroundTarget.value : '#ffffff',
            finderColor: this.hasFinderColorTarget ? this.finderColorTarget.value : null,
            alignmentShape: this.hasAlignmentShapeTarget ? this.alignmentShapeTarget.value : 'square',
            alignmentColor: this.hasAlignmentColorTarget ? this.alignmentColorTarget.value : null,
            moduleScale: this.hasModuleScaleTarget ? Number(this.moduleScaleTarget.value) : 0.92,
            gradientType: this.hasGradientTypeTarget ? this.gradientTypeTarget.value : 'none',
            gradientTo: this.hasGradientToTarget && this.gradientTypeTarget.value !== 'none' ? this.gradientToTarget.value : null,
            logoHref: this.hasLogoHrefTarget && this.logoHrefTarget.value ? this.logoHrefTarget.value : null,
            finderIconHref: this.hasFinderIconHrefTarget && this.finderIconHrefTarget.value ? this.finderIconHrefTarget.value : null,
            finderEffect: this.hasFinderEffectTarget ? this.finderEffectTarget.value : 'none',
            finderGradientTo: this.hasFinderEffectTarget && this.finderEffectTarget.value === 'gradient' && this.hasFinderGradientToTarget ? this.finderGradientToTarget.value : null,
            finderEyeShape: this.hasFinderEyeShapeTarget && this.finderEyeShapeTarget.value ? this.finderEyeShapeTarget.value : null,
            frameShape: this.hasFrameShapeTarget ? this.frameShapeTarget.value : 'none',
            frameLabel: this.hasFrameLabelTarget && this.frameLabelTarget.value ? this.frameLabelTarget.value : null,
            errorCorrection: 'H',
        }
        if (this._userPresetConfig) {
            Object.assign(base, this._userPresetConfig)
            base.preset = null
        }
        return base
    }

    downloadSvg() {
        if (!this.hasPreviewTarget) return
        const svg = this.previewTarget.querySelector('svg')
        if (!svg) return
        this.downloadBlob(new Blob([svg.outerHTML], { type: 'image/svg+xml' }), 'qrcode.svg')
    }

    async downloadPng() {
        try {
            const response = await fetch(this.downloadEndpointValue, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'image/png' },
                body: JSON.stringify(this.payload()),
            })
            if (!response.ok) throw new Error(await response.text())
            this.downloadBlob(await response.blob(), 'qrcode.png')
        } catch (error) {
            this.setStatus(error.message || 'PNG export failed.')
        }
    }

    downloadBlob(blob, filename) {
        const url = URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.href = url
        a.download = filename
        a.click()
        URL.revokeObjectURL(url)
    }

    setStatus(message) {
        if (this.hasStatusTarget) this.statusTarget.textContent = message
    }
}
