import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
    static targets = [
        'content', 'preset', 'moduleShape', 'finderShape', 'alignmentShape', 'foreground', 'background',
        'finderColor', 'alignmentColor', 'gradientType', 'gradientTo', 'size', 'margin', 'moduleScale', 'finderEyeScale',
        'logoHref', 'finderIconHref', 'finderEffect', 'finderGradientTo', 'finderEyeShape', 'frameShape', 'frameLabel',
        'testProfile', 'preview', 'readabilityGauge', 'status', 'presetPopup'
    ]

    static values = {
        endpoint: { type: String, default: '/api/qrcode/svg' },
        downloadEndpoint: { type: String, default: '/api/qrcode/png' },
        validateEndpoint: { type: String, default: '/api/qrcode/validate' },
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
        const renderId = this._renderId = (this._renderId || 0) + 1
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

            const svg = await response.text()
            if (renderId !== this._renderId) return
            this.previewTarget.innerHTML = svg
            await this.updateReadability(renderId)
        } catch (error) {
            if (renderId !== this._renderId) return
            this.setStatus(error.message || 'Unable to render QR code.')
        }
    }

    async updateReadability(renderId) {
        const response = await fetch(this.validateEndpointValue, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(this.payload()),
        })
        const data = await response.json()
        if (renderId !== this._renderId) return

        const prefix = `Lisibilité estimée : ${data.readabilityScore ?? 0} %`
        if (this.hasReadabilityGaugeTarget) this.readabilityGaugeTarget.value = data.readabilityScore ?? 0
        const details = data.errors?.length
            ? data.errors
            : (data.warnings?.length ? data.warnings : [])
        const decode = await this.verifyAutomaticDecode()
        if (renderId !== this._renderId) return
        const criteria = data.readabilityDetails
            ? `marge ${data.readabilityDetails.margin}, modules ${data.readabilityDetails.moduleScale}, œil ×${data.readabilityDetails.finderEyeScale}, correction ${data.readabilityDetails.errorCorrection}`
            : ''
        const message = details.length ? `${prefix} — ${details.join(' ')}` : prefix
        this.setStatus(`${message}${criteria ? ` — ${criteria}` : ''} — ${decode}`)
    }

    optimizeReadability() {
        this._userPresetConfig = null
        this.presetValue = ''
        if (this.hasPresetTarget) this.presetTarget.value = ''
        if (this.hasMarginTarget) this.marginTarget.value = '4'
        if (this.hasModuleScaleTarget) this.moduleScaleTarget.value = '0.92'
        if (this.hasFinderEyeScaleTarget) this.finderEyeScaleTarget.value = '1'
        if (this.hasModuleShapeTarget) this.moduleShapeTarget.value = 'square'
        if (this.hasFinderShapeTarget) this.finderShapeTarget.value = 'square'
        if (this.hasFinderEyeShapeTarget) this.finderEyeShapeTarget.value = 'square'
        if (this.hasAlignmentShapeTarget) this.alignmentShapeTarget.value = 'square'
        if (this.hasForegroundTarget) this.foregroundTarget.value = '#111111'
        if (this.hasBackgroundTarget) this.backgroundTarget.value = '#ffffff'
        if (this.hasFinderColorTarget) this.finderColorTarget.value = '#111111'
        if (this.hasAlignmentColorTarget) this.alignmentColorTarget.value = '#111111'
        if (this.hasGradientTypeTarget) this.gradientTypeTarget.value = 'none'
        if (this.hasFinderEffectTarget) this.finderEffectTarget.value = 'none'
        if (this.hasLogoHrefTarget) this.logoHrefTarget.value = ''
        if (this.hasFinderIconHrefTarget) this.finderIconHrefTarget.value = ''
        if (this.hasFrameShapeTarget) this.frameShapeTarget.value = 'none'
        this.refresh()
    }

    async verifyAutomaticDecode() {
        if (!('BarcodeDetector' in window)) return 'Décodage automatique non disponible'
        const svg = this.previewTarget.querySelector('svg')
        if (!svg) return 'Décodage automatique impossible'

        const url = URL.createObjectURL(new Blob([svg.outerHTML], { type: 'image/svg+xml' }))
        try {
            const image = new Image()
            await new Promise((resolve, reject) => {
                image.onload = resolve
                image.onerror = reject
                image.src = url
            })
            const detector = new BarcodeDetector({ formats: ['qr_code'] })
            let successes = 0
            for (const size of this.testSizes()) {
                const canvas = document.createElement('canvas')
                canvas.width = size
                canvas.height = size
                canvas.getContext('2d').drawImage(image, 0, 0, size, size)
                const results = await detector.detect(canvas)
                if (results.some(result => result.rawValue)) successes++
            }
            return `Décodage automatique : ${successes}/${this.testSizes().length} résolutions`
        } catch (_) {
            return 'Décodage automatique non disponible'
        } finally {
            URL.revokeObjectURL(url)
        }
    }

    testSizes() {
        const profile = this.hasTestProfileTarget ? this.testProfileTarget.value : 'balanced'
        return profile === 'compact' ? [96, 128, 256] : (profile === 'print' ? [256, 512, 1024] : [128, 256, 512])
    }

    payload() {
        const preset = this.presetValue || (this.hasPresetTarget ? this.presetTarget.value : '')
        const base = {
            content: this.hasContentTarget ? this.contentTarget.value : this.contentValue,
            preset: preset || null,
            size: this.hasSizeTarget ? Number(this.sizeTarget.value) : 320,
            margin: this.hasMarginTarget ? Number(this.marginTarget.value) : 4,
            moduleShape: this.hasModuleShapeTarget ? this.moduleShapeTarget.value : 'square',
            finderFrameShape: this.hasFinderShapeTarget ? this.finderShapeTarget.value : 'square',
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
            finderEyeScale: this.hasFinderEyeScaleTarget ? Number(this.finderEyeScaleTarget.value) : 1,
            testProfile: this.hasTestProfileTarget ? this.testProfileTarget.value : 'balanced',
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
